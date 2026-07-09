<?php session_start();
    //-- CONSTANTES --
    define("NIVEL","../");
    
    //-- PARAMETROS Y VARIABLES --
    $id = $_POST["id"];
    $nombre = trim($_POST["nombre"]);
    $ap_paterno = trim($_POST["ap_paterno"]);
    $ap_materno = trim($_POST["ap_materno"]);
    $correo = trim($_POST["correo"]);
    $rol = $_POST["rol"];
    $area = $_POST["area"];
    $status = $_POST["status"];
    $actualiza = ($_POST["status"]==1)? true : false ;
    $usuario = trim($_POST["usuario"]);
    $contrasena = hash('sha256', trim($_POST["contrasena"]));

    //-- CONECTA A LA BASE DE DATOS --
    include(NIVEL."com/configBD.php");

    //-- SI NO EXISTE EL USUARIO LO CREA --
    if($id==0){
        $sql = "INSERT INTO tbl_usuarios (nombre,
                                          ap_paterno,
                                          ap_materno,
                                          usuario,
                                          contrasena,
                                          correo,
                                          rol,
                                          area,
                                          status)
                VALUES ('$nombre',
                        '$ap_paterno',
                        '$ap_materno',
                        '$usuario',
                        '$contrasena',
                        '$correo',
                        $rol,
                        $area,
                        $status)";
    }

    //-- SI YA EXISTE EL USUARIO LO ACTUALIZA --
    if($id>0){
        $sql = "UPDATE tbl_usuarios
                SET nombre = '$nombre',
                    ap_paterno = '$ap_paterno',
                    ap_materno = '$ap_materno',
                    correo = '$correo',
                    rol = $rol,
                    area = $area,
                    status = $status";
        if($actualiza) $sql .= ", usuario = '$usuario', contrasena = '$contrasena' ";
        $sql .= "WHERE id = $id";
    }
    $db->execute($sql);

    //-- CIERRA CONEXION A LA BASE DE DATOS --
    $db->close();
?>