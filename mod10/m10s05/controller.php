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
        case "PRIVILEGIOS":
            edita_privilegios();
        break;
        case "RETIRAR_USUARIO":
            retirar_usuario();
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
        $info["id_aplicacion"] = ($_POST["id_aplicacion"]>0) ? $_POST["id_aplicacion"] : 0 ;
        $info["error"] = 0;
        $info["titulo"] = "";
        $info["mensaje"] = "Datos guardados correctamente";
        $info["aceptar"] = 0;
        $info["timer"] = 0;

        $param["nombre"] = trim($_POST["nombre"]);
        $param["ap_paterno"] = trim($_POST["ap_paterno"]);
        $param["ap_materno"] = trim($_POST["ap_materno"]);
        $param["correo"] = trim($_POST["correo"]);
        $param["rol"] = $_POST["rol"];
        $param["area"] = $_POST["area"];
        $param["status"] = intval($_POST["status"]);
        $condition = "id = ".$info["id"];

        try{
            $dbAdmin->BeginTrans();

            //-- ACTUALIZA USUARIO --
            if($info["id"]>0){
                if( $dbAdmin->autoExecute("tbl_usuarios",$param,"UPDATE",$condition) === FALSE ){ throw new Exception("Error al actualizar registro".$dbAdmin->errorMsg(), 100); }
            }

            //-- CREA NUEVO USUARIO --
            else{
                if( $dbAdmin->autoExecute("tbl_usuarios",$param,"INSERT") === FALSE ){ throw new Exception("Error al insertar registro".$dbAdmin->errorMsg(), 100); }
                else $info["id"] = $dbAdmin->Insert_ID();
            }

            //-- SI SE EDITA UN USUARIO DE UNA APLICACION --
            if($info["id_aplicacion"]>0 && $param["rol"]>0 && $param["area"]>0){

                //-- OBTIENE ROLES DE LA APLICACION --
                $prepare = $dbAdmin->prepare("SELECT id FROM cat_roles WHERE aplicacion = ? AND status = ?");
                $result = $dbAdmin->execute($prepare, [$info["id_aplicacion"], 1]);
                while(!$result->EOF){

                    //-- BORRA ROLES DE LA APLICACION RELACIONADOS AL USUARIO --
                    if( $dbAdmin->execute("DELETE FROM tbl_usuarios_rol WHERE id_usuario = ".$info["id"]." AND id_rol = ".$result->fields[0]) === FALSE ){ throw new Exception("Error al borrar registro".$dbAdmin->errorMsg(), 100); }

                    $result->MoveNext();
                }

                //-- RELACIONA ROL AL USUARIO --
                $paramRol = array( "id_usuario" => $info["id"], "id_rol" => $param["rol"] );
                if( $dbAdmin->autoExecute("tbl_usuarios_rol",$paramRol,"INSERT") === FALSE ){ throw new Exception("Error al asignar rol".$dbAdmin->errorMsg(), 100); }

                //-- OBTIENE AREAS DE LA APLICACION --
                $prepare = $dbAdmin->prepare("SELECT id FROM cat_areas WHERE aplicacion = ? AND status = ?");
                $result = $dbAdmin->execute($prepare, [$info["id_aplicacion"], 1]);
                while(!$result->EOF){

                    //-- BORRA AREAS DE LA APLICACION RELACIONADOS AL USUARIO --
                    if( $dbAdmin->execute("DELETE FROM tbl_usuarios_area WHERE id_usuario = ".$info["id"]." AND id_area = ".$result->fields[0]) === FALSE ){ throw new Exception("Error al borrar registro".$dbAdmin->errorMsg(), 100); }

                    $result->MoveNext();
                }

                //-- RELACIONA AREA AL USUARIO --
                $paramArea = array( "id_usuario" => $info["id"], "id_area" => $param["area"] );
                if( $dbAdmin->autoExecute("tbl_usuarios_area",$paramArea,"INSERT") === FALSE ){ throw new Exception("Error al asignar area".$dbAdmin->errorMsg(), 100); }
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
                    throw new Exception("Error al subir archivo", 120);
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

    function edita_privilegios(){
        global $dbAdmin;
        $info = $param = array();

        $id_usuario = (isset($_POST["id_usuario"])) ? $_POST["id_usuario"] : 0 ;
        $id_aplicacion = (isset($_POST["id_aplicacion"])) ? $_POST["id_aplicacion"] : 0 ;
        $menus = (isset($_POST["menus"])) ? $_POST["menus"] : array() ;

        $info["id"] = $id_usuario;
        $info["error"] = 0;
        $info["titulo"] = "";
        $info["mensaje"] = "Datos guardados correctamente";
        $info["aceptar"] = 0;
        $info["timer"] = 0;

        try{
            $dbAdmin->BeginTrans();

            //-- ELIMINA PRIVILEGIOS DEL MENU --
            if( $dbAdmin->execute("DELETE FROM tbl_usuarios_menu WHERE id_usuario = ".$id_usuario." AND id_menu IN (SELECT id FROM tbl_menu WHERE sistema = ".$id_aplicacion." AND status = 1)") === FALSE ){ throw new Exception("Error al borrar privilegios".$dbAdmin->errorMsg(), 100); }

            //-- ASIGNA PRIVILEGIOS DEL MENU AL USUARIO --
            for($x=0;$x<count($menus);$x++){
                $param = array( "id_usuario" => $id_usuario, "id_menu" => $menus[$x] );
                if( $dbAdmin->autoExecute("tbl_usuarios_menu",$param,"INSERT") === FALSE ){ throw new Exception("Error al asignar privilegios".$dbAdmin->errorMsg(), 100 ); }
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

    function retirar_usuario(){
        global $dbAdmin;
        $info = $param = array();

        $id_usuario = (isset($_POST["id_usuario"])) ? $_POST["id_usuario"] : 0 ;
        $id_aplicacion = (isset($_POST["id_aplicacion"])) ? $_POST["id_aplicacion"] : 0 ;

        $info["id"] = $id_usuario;
        $info["error"] = 0;
        $info["titulo"] = "";
        $info["mensaje"] = "Usuario retirado correctamente";
        $info["aceptar"] = 0;
        $info["timer"] = 0;

        try{
            $dbAdmin->BeginTrans();

            //-- ELIMINA USUARIO DE LA APLICACION --
            if( $dbAdmin->execute("DELETE FROM tbl_usuarios_aplicacion WHERE id_usuario = ".$id_usuario." AND id_aplicacion = ".$id_aplicacion) === FALSE ){ throw new Exception("Error al retirar usuario".$dbAdmin->errorMsg(), 100); }

            //-- ELIMINA PRIVILEGIOS DEL MENU DEL USUARIO --
            if( $dbAdmin->execute("DELETE FROM tbl_usuarios_menu WHERE id_usuario = ".$id_usuario." AND id_menu IN (SELECT id FROM tbl_menu WHERE sistema = ".$id_aplicacion." AND status = 1)") === FALSE ){ throw new Exception("Error al borrar privilegios".$dbAdmin->errorMsg(), 100); }

            //-- ELIMINA ROLES DEL USUARIO --
            if( $dbAdmin->execute("DELETE FROM tbl_usuarios_rol WHERE id_usuario = ".$id_usuario." AND id_rol IN (SELECT id FROM cat_roles WHERE aplicacion = ".$id_aplicacion." AND status = 1)") === FALSE ){ throw new Exception("Error al borrar roles".$dbAdmin->errorMsg(), 100); }

            //-- ELIMINA AREAS DEL USUARIO --
            if( $dbAdmin->execute("DELETE FROM tbl_usuarios_area WHERE id_usuario = ".$id_usuario." AND id_area IN (SELECT id FROM cat_areas WHERE aplicacion = ".$id_aplicacion." AND status = 1)") === FALSE ){ throw new Exception("Error al borrar areas".$dbAdmin->errorMsg(), 100); }

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