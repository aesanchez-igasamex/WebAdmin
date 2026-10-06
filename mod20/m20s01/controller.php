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
        //-- EDITA REGISTRO --
        case "EDITAR":
            edita_registro();
            break;
    }

    //-- CIERRA CONEXION A LA BASE DE DATOS --
    $dbAdmin->close();



    /**
     * Editar Registro
     *
     * Crea o actualiza un registro.
     */
    function edita_registro(){
        global $dbAdmin;
        $info = $param = array();

        $info["id"] = $_POST["id"];
        $info["error"] = 0;
        $info["titulo"] = "";
        $info["mensaje"] = "Datos guardados correctamente";
        $info["aceptar"] = 0;
        $info["timer"] = 0;

        $param["clave"] = trim($_POST["clave"]);
        $param["nombre"] = trim($_POST["nombre"]);
        $param["abreviatura"] = trim($_POST["abreviatura"]);
        $param["status"] = intval($_POST["status"]);
        $condition = "id = ".$info["id"];

        try{
            $dbAdmin->BeginTrans();
            if($info["id"]>0){
                if( $dbAdmin->autoExecute("cat_actividad_regulada",$param,"UPDATE",$condition) === FALSE ){ throw new Exception("Error al actualizar registro".$dbAdmin->errorMsg(), 100); }
                else{ guardaBitacora(APLICACION,"Actualiza registro de Actividad Regulada", $_SESSION["usuario_nombre"], "cat_actividad_regulada", $info["id"], ""); }
            }
            else{
                if( $dbAdmin->autoExecute("cat_actividad_regulada",$param,"INSERT") === FALSE ){ throw new Exception("Error al insertar registro".$dbAdmin->errorMsg(), 100); }
                else{
                    $info["id"] = $dbAdmin->Insert_ID();
                    guardaBitacora(APLICACION,"Inserta nuevo registro de Actividad Regulada", $_SESSION["usuario_nombre"], "cat_actividad_regulada", $info["id"], "");
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