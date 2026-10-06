<?php
    //-- CONSTANTES --
    define("NIVEL","../");

    //-- CONECTA A LA BASE DE DATOS --
    include(NIVEL."../com/configBD_Admin.php");
    include(NIVEL."../com/configBD_SAG.php");

    $sql = "SELECT *
            FROM clientes_emr
            WHERE status = 1";
    $prepare = $dbAdmin->prepare($sql);
    $result1 = $dbAdmin->execute($prepare,$params);
?>