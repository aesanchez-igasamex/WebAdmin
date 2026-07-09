<?php
    //-- CONSTANTES --
    $protocol = (isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"]=="on" || $_SERVER["HTTPS"] == 1)) ? "https://" : "http://";
    define("RUTA",$protocol.$_SERVER["HTTP_HOST"]."/WebAdmin");

    //-- CONECTA A LA BASE DE DATOS --
    defined('NIVEL')? NIVEL : define("NIVEL","../");
    include(NIVEL."../com/configBD_Admin.php");

    //-- CIERRA SESSION ACCESO --
    session_start();
    $values["en_linea"] = 0;
    $values["no_sesion"] = "";
    $condition = "no_sesion = '".session_id()."'";
    $dbAdmin->autoExecute("tbl_control_usuarios",$values,"UPDATE",$condition);
    $dbAdmin->close();
    session_destroy();

    echo RUTA;
?>