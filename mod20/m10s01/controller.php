<?php
    //-- CONSTANTES --
    define("NIVEL","../");

    session_start();
    
    //-- PARAMETROS Y VARIABLES --
    $accion = $_POST["accion"];

    //-- CONECTA A LA BASE DE DATOS --
    include(NIVEL."../com/configBD_Admin.php");

    //-- CONTROLADOR DE ACCIONES --
    switch ($accion) {

        //-- EDITA REGISTRO --
        case "EDITAR":
            editar_registro();
        break;
    }

    //-- CIERRA CONEXION A LA BASE DE DATOS --
    $db->close();


    function editar_registro(){
        global $dbCSYMA;
        $id = $_POST["id"];

        $param["nombre"] = trim($_POST["nombre"]);
        $param["descripcion"] = trim($_POST["descripcion"]);
        $condition = "id = ".$id;

        if( $dbCSYMA->autoExecute("cat_aplicaciones",$param,"UPDATE",$condition) === FALSE ){
            $info["id"] = $id;
            $info["error"] = 1;
            $info["titulo"] = "ERROR";
            $info["mensaje"] = "Error al actualizar registro";
        }
        else {  
            $info["id"] = $id;
            $info["error"] = 0;
            $info["titulo"] = "¡Datos guardados exitosamente!";
            $info["mensaje"] = "Se actualizo el registro";
        }
        
        echo json_encode($info);
    }
?>