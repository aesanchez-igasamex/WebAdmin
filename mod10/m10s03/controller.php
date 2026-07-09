<?php
    //-- CONSTANTES --
    define("NIVEL","../../");
    define("APLICACION","100");
    session_start();

    //-- CONECTA A LA BASE DE DATOS --
    include(NIVEL."../com/config.php");
    $dbAdmin = conectaBD("mysql","admin");

    //-- CONTROLADOR DE ACCIONES --
    switch ($_POST["accion"]) {
        //-- EDITA INFORMACION DEL USUARIO --
        case "USUARIO":
            edita_usuario();
        break;
        case "ASIGNAR_USUARIOS":
            asignar_usuarios();
        break;
    }

    //-- CIERRA CONEXION A LA BASE DE DATOS --
    $dbAdmin->close();


    /**
     * Editar Usuario
     *
     * Crea o actualiza registro de un usuario.
     */
    function edita_usuario(){
        global $dbAdmin;
        $info = $param = array();

        $info["id"] = $_POST["id"];
        $info["error"] = 0;
        $info["titulo"] = "";
        $info["mensaje"] = "Datos guardados correctamente";
        $info["aceptar"] = 0;
        $info["timer"] = 0;

        $param["nombre"] = trim($_POST["nombre"]);
        $param["ap_paterno"] = trim($_POST["ap_paterno"]);
        $param["ap_materno"] = trim($_POST["ap_materno"]);
        $param["correo"] = trim($_POST["correo"]);
        $param["status"] = intval($_POST["status"]);
        $condition = "id = ".$info["id"];

        try{
            $dbAdmin->BeginTrans();
            if($info["id"]>0){
                if( $dbAdmin->autoExecute("tbl_usuarios",$param,"UPDATE",$condition) === FALSE ){ throw new Exception("Error al actualizar registro".$dbAdmin->errorMsg()); }
            }
            else{
                if( $dbAdmin->autoExecute("tbl_usuarios",$param,"INSERT") === FALSE ){ throw new Exception("Error al insertar registro".$dbAdmin->errorMsg()); }
                else $info["id"] = $dbAdmin->Insert_ID();
            }

            //-- SI EXISTE ARCHIVO --
            if( isset($_FILES["imagen"]) && $_FILES["imagen"]["error"] == 0 ){
                $ext = pathinfo($_FILES["imagen"]["name"], PATHINFO_EXTENSION);
                $nombreArchivo = "imagen_".$info["id"].".".$ext;
                $rutaArchivo = "imagenes/".$nombreArchivo;

                if( move_uploaded_file($_FILES["imagen"]["tmp_name"], $rutaArchivo) ){
                    $param["foto"] = $nombreArchivo;
                    if( $dbAdmin->autoExecute("tbl_usuarios",$param,"UPDATE",$condition) === FALSE ){ throw new Exception("Error al actualizar registro".$dbAdmin->errorMsg(), 100 ); }
                }
                else{
                    throw new Exception("Error al subir archivo");
                }
            }

            $dbAdmin->CommitTrans();
        }
        catch(Exception $e){
            $dbAdmin->RollbackTrans();
            $log = guardaLog(APLICACION, $e->getCode(), $e->__toString());
            $info["error"] = 1;
            $info["titulo"] = "ERROR";
            $info["mensaje"] = $log["msg"];
            $info["aceptar"] = 1;
        }
        echo json_encode($info);
    }

    /**
     * Asignar Usuarios
     *
     * Asigna usuarios a la aplicación.
     */
    function asignar_usuarios(){
        global $dbAdmin;
        $info = $param = array();

        $id_aplicacion = (isset($_POST["id_aplicacion"])) ? $_POST["id_aplicacion"] : 0 ;
        $usuarios = (isset($_POST["usuarios"])) ? $_POST["usuarios"] : array() ;
        $info["id"] = $id_aplicacion;
        $info["error"] = 0;
        $info["titulo"] = "";
        $info["mensaje"] = "Datos guardados correctamente";
        $info["aceptar"] = 0;
        $info["timer"] = 0;

        try{
            $dbAdmin->BeginTrans();

            for($x=0;$x<count($usuarios);$x++){
                $param = array( "id_usuario" => $usuarios[$x], "id_aplicacion" => $id_aplicacion );
                $valores = "id_usuario = ".$usuarios[$x]." AND id_aplicacion = ".$id_aplicacion;
                if( $dbAdmin->autoExecute("tbl_usuarios_aplicacion",$param,"INSERT") === FALSE ){ throw new Exception("Error al asignar usuario: $valores ".$dbAdmin->errorMsg(), 100 ); }
            }

            $dbAdmin->CommitTrans();
        }
        catch(Exception $e){
            $dbAdmin->RollbackTrans();
            $log = guardaLog(APLICACION, $e->getCode(), $e->__toString());
            $info["error"] = 1;
            $info["titulo"] = "ERROR";
            $info["mensaje"] = $log["msg"];
            $info["aceptar"] = 1;
        }
        echo json_encode($info);
    }
?>