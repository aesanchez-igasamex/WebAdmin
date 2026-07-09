<?php
    //-- CONSTANTES --
    define("NIVEL","../../");

    //-- VERIFICA SI EXISTE SESION --
    session_start();
    if(empty($_SESSION["id_usuario"])){
        session_destroy();
        echo "<script>window.parent.cerrarRecargar();</script>";
    }

    //-- OBTIENE INFORMACION DE LA SESION --
    $nombre_usuario = $_SESSION["nombre_usuario"];

    //-- PARAMETROS Y VARIABLES --
    $id_sistema = $_POST["id_sistema"];
    $cadCoords = (strlen($_POST["coordenadas"])>0)? substr($_POST["coordenadas"],0,-1) : "" ;

    //-- CONECTA A LA BASE DE DATOS --
    include(NIVEL."com/configBD.php");

    //-- BORRA COORDENADAS --
    $conexion->query("DELETE FROM tbl_mapa WHERE sistema = $id_sistema AND tipo = 'linea'");

    //-- ACTUALIZA COORDENADAS --
    $coordenadas = explode("|",$cadCoords);
    for($x=0;$x<count($coordenadas);$x++){
        $coords = explode(",", $coordenadas[$x]);
        $latitud = $coords[0];
        $longitud = $coords[1];
        $sql = "INSERT INTO tbl_mapa
        VALUES (NULL,
                $id_sistema,
                0,
                '$latitud',
                '$longitud',
                'linea',
                'Ducto')";
        $conexion->query($sql);
    }

    //-- GUARDA EN HISTORIAL --
    $conexion->query("INSERT INTO tbl_historial VALUES (NULL,'Modifica Ducto','$nombre_usuario','tbl_mapa',$id_sistema,'".addslashes($cadCoords)."',NOW())");

    //-- CIERRA CONEXION A LA BASE DE DATOS --
    $conexion -> close();
?>