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
        //-- EDITA REGISTRO DEL APLICATIVO --
        case "EDITAR":
            edita_aplicacion();
            break;

        //-- EDITA REGISTRO DE MENÚ --
        case "EDITAR_MENU":
            editar_menu();
            break;

        //-- EDITA REGISTRO DE SUBMENÚ --
        case "EDITAR_SUBMENU":
            editar_submenu();
            break;
    }

    //-- CIERRA CONEXION A LA BASE DE DATOS --
    $dbAdmin->close();



    /**
     * Editar Aplicacion
     *
     * Crea o actualiza registro de una aplicación.
     */
    function edita_aplicacion(){
        global $dbAdmin;
        $info = $param = array();

        $info["id"] = $_POST["id"];
        $info["error"] = 0;
        $info["titulo"] = "";
        $info["mensaje"] = "Datos guardados correctamente";
        $info["aceptar"] = 0;
        $info["timer"] = 0;

        $param["nombre"] = trim($_POST["nombre"]);
        $param["descripcion"] = trim($_POST["descripcion"]);
        $param["link"] = trim($_POST["link"]);
        $param["minutos_sesion"] = intval($_POST["minutos_sesion"]);
        $param["observaciones"] = trim($_POST["observaciones"]);
        $param["status"] = intval($_POST["status"]);
        $condition = "id = ".$info["id"];

        try{
            $dbAdmin->BeginTrans();
            if($info["id"]>0){
                if( $dbAdmin->autoExecute("cat_aplicaciones",$param,"UPDATE",$condition) === FALSE ){ throw new Exception("Error al actualizar registro".$dbAdmin->errorMsg(), 100); }
                else{ guardaBitacora(APLICACION,"Actualiza registro de Aplicación", $_SESSION["usuario_nombre"], "cat_aplicaciones", $info["id"], ""); }
            }
            else{
                if( $dbAdmin->autoExecute("cat_aplicaciones",$param,"INSERT") === FALSE ){ throw new Exception("Error al insertar registro".$dbAdmin->errorMsg(), 100); }
                else{
                    $info["id"] = $dbAdmin->Insert_ID();
                    guardaBitacora(APLICACION,"Inserta nuevo registro de Aplicación", $_SESSION["usuario_nombre"], "cat_aplicaciones", $info["id"], "");
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
     * Editar Menú
     *
     * Crea o actualiza el registro de un menú.
     */
    function editar_menu(){
        global $dbAdmin;
        $info = $param = array();
        $existe = false;

        $info["id"] = ($_POST["id"]>0) ? intval($_POST["id"]) : 0 ;
        $info["error"] = 0;
        $info["titulo"] = "";
        $info["mensaje"] = "Datos guardados correctamente";
        $info["aceptar"] = 0;
        $info["timer"] = 0;

        $param["id"] = intval($_POST["id"]);
        $param["sistema"] = intval($_POST["id_aplicacion"]);
        $param["nombre"] = trim($_POST["nombre"]);
        $param["identificador"] = trim($_POST["identificador"]);
        $param["directorio"] = trim($_POST["directorio"]);
        $param["icono"] = trim($_POST["icono"]);
        $param["orden"] = intval($_POST["orden"]);
        $param["status"] = intval($_POST["status"]);
        $param["tipo"] = 1;
        $param["padre"] = 0;
        $condition = "id = ".$info["id"];

        try{
            $dbAdmin->BeginTrans();

            //-- VERIFICA SI EL REGISTRO EXISTE --
            $prepare = $dbAdmin->prepare("SELECT * FROM tbl_menu WHERE id = ?");
            $result = $dbAdmin->execute($prepare,[$info["id"]]);
            if(!$result) throw new Exception("Error al obtener registro".$dbAdmin->errorMsg(), 100);
            $existe = ($result->EOF)? false : true;

            //-- SI EXISTE, ACTUALIZA --
            if($existe){
                if( $dbAdmin->autoExecute("tbl_menu",$param,"UPDATE",$condition) === FALSE ){ throw new Exception("Error al actualizar registro".$dbAdmin->errorMsg(), 100); }
                else{ guardaBitacora(APLICACION,"Actualiza registro de Menú", $_SESSION["usuario_nombre"], "tbl_menu", $info["id"], ""); }

                //-- INACTIVA SUBMENUS SI EL MENÚ SE INACTIVA --
                if($param["status"]==0){
                    $paramSub["status"] = $param["status"];
                    $conditionSub = "padre = ".$info["id"];
                    if( $dbAdmin->autoExecute("tbl_menu",$paramSub,"UPDATE",$conditionSub) === FALSE ){ throw new Exception("Error al actualizar submenús".$dbAdmin->errorMsg(), 100); }
                }
            }

            // SI NO EXISTE, INSERTA --
            else{
                if( $dbAdmin->autoExecute("tbl_menu",$param,"INSERT") === FALSE ){ throw new Exception("Error al insertar registro".$dbAdmin->errorMsg(), 100); }
                else{
                    $info["id"] = $dbAdmin->Insert_ID();
                    guardaBitacora(APLICACION,"Inserta nuevo registro de Menú", $_SESSION["usuario_nombre"], "tbl_menu", $info["id"], "");
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
     * Editar Submenú
     *
     * Crea o actualiza el registro de un submenú.
     */
    function editar_submenu(){
        global $dbAdmin;
        $info = $param = array();

        $info["id"] = ($_POST["id"]>0) ? intval($_POST["id"]) : 0 ;
        $info["error"] = 0;
        $info["titulo"] = "";
        $info["mensaje"] = "Datos guardados correctamente";
        $info["aceptar"] = 0;
        $info["timer"] = 0;

        $param["id"] = intval($_POST["id"]);
        $param["sistema"] = intval($_POST["id_aplicacion"]);
        $param["nombre"] = trim($_POST["nombre"]);
        $param["identificador"] = trim($_POST["identificador"]);
        $param["directorio"] = trim($_POST["directorio"]);
        $param["icono"] = trim($_POST["icono"]);
        $param["orden"] = floatval($_POST["orden"]);
        $param["status"] = intval($_POST["status"]);
        $param["padre"] = intval($_POST["padre"]);
        $param["tipo"] = 2;
        $condition = "id = ".$info["id"];

        try{
            $dbAdmin->BeginTrans();

            //-- VERIFICA SI EL REGISTRO EXISTE --
            $prepare = $dbAdmin->prepare("SELECT * FROM tbl_menu WHERE id = ?");
            $result = $dbAdmin->execute($prepare,[$info["id"]]);
            if(!$result) throw new Exception("Error al obtener registro".$dbAdmin->errorMsg(), 100);
            $existe = ($result->EOF)? false : true;

            if($existe){
                if( $dbAdmin->autoExecute("tbl_menu",$param,"UPDATE",$condition) === FALSE ){ throw new Exception("Error al actualizar registro".$dbAdmin->errorMsg(), 100); }
                else{ guardaBitacora(APLICACION,"Actualiza registro de Submenú", $_SESSION["usuario_nombre"], "tbl_menu", $info["id"], ""); }
            }
            else{
                if( $dbAdmin->autoExecute("tbl_menu",$param,"INSERT") === FALSE ){ throw new Exception("Error al insertar registro".$dbAdmin->errorMsg(), 100); }
                else{
                    $info["id"] = $dbAdmin->Insert_ID();
                    guardaBitacora(APLICACION,"Inserta nuevo registro de Submenú", $_SESSION["usuario_nombre"], "tbl_menu", $info["id"], "");
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
?>