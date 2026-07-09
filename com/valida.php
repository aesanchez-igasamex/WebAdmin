<?php
    //-- CONSTANTES --
    define("NIVEL", "../");
    define("APLICACION", 100);

    //-- DEFINE ZONA HORARIA --
    date_default_timezone_set("America/Mexico_City");

    //-- INICIALIZA SESSION --
    session_start();

    //-- VALIDA ACCESO --
    $val = validaAcceso();

    //-- REDIRECCIONA EN CASO DE ERROR --
    if ($val["error"]) {
        $_SESSION["login_error"] = 1;
        $_SESSION["login_error_msg"] = $val["error_msg"];
    }
    header("Location: ".$val["ruta"]);
    exit;


    function validaAcceso(){
        $usuario = $_POST["usuario"];
        $contrasena = crypt($_POST["contrasena"], '$2a$07$usesomesillystringforsalt$');
        $control = $values = array();

        try{
            include(NIVEL."../com/config.php");
            $dbAdmin = conectaBD("mysql","admin");

            //-- CIERRA SESIONES EXPIRADAS --
            $sql = "SELECT a.id_usuario, b.minutos_sesion
                    FROM tbl_control_usuarios AS a
                    JOIN cat_aplicaciones AS b ON a.id_aplicacion = b.id";
            $prepare = $dbAdmin->prepare($sql);
            $resCtrl = $dbAdmin->execute($prepare);
            while (!$resCtrl->EOF) {
                $values["en_linea"] = 0;
                $values["no_sesion"] = "";
                $condition = "id_usuario = ".$resCtrl->fields[0]." AND ultimo_acceso <= DATE_ADD(NOW(), INTERVAL -".$resCtrl->fields[1]." MINUTE)";
                $dbAdmin->autoExecute("tbl_control_usuarios",$values,"UPDATE",$condition);
                $resCtrl->MoveNext();
            }

            //-- BUSCA INFORMACION DEL USUARIO --
            $sql = "SELECT a.id, a.nombre, a.ap_paterno, a.ap_materno, a.foto
                    FROM tbl_usuarios AS a
                    JOIN tbl_usuarios_aplicacion AS b ON a.id = b.id_usuario
                    WHERE b.id_aplicacion = ?
                    AND a.usuario = ?
                    AND a.contrasena = ?
                    AND a.status = ?";
            $prepare = $dbAdmin->prepare($sql);
            $resUsr = $dbAdmin->execute($prepare, [APLICACION, $usuario, $contrasena, 1]);
            if(!$resUsr) throw new Exception("Error al ejecutar consulta: ".$dbAdmin->errorMsg(), 100);
            if($resUsr->EOF) throw new Exception("Usuario o contraseña incorrectos ($usuario)", 110);

                //-- DATOS DEL USUARIO --
                $_SESSION["usuario_id"] = $resUsr->fields[0];
                $_SESSION["usuario_nombre"] = $resUsr->fields[1]." ".$resUsr->fields[2]." ".$resUsr->fields[3];
                $_SESSION["usuario_foto"] = (strlen($resUsr->fields[4])>0)? $resUsr->fields[4] : "default.png" ;
                $_SESSION["session_id"] = session_id();
                //$_SESSION["aplicacion"] = APLICACION;

                //-- BUSCA EL ROL ASIGNADO AL USUARIO --
                $sql = "SELECT a.id_rol, b.nombre
                        FROM tbl_usuarios_rol AS a
                        JOIN cat_roles AS b ON a.id_rol = b.id
                        WHERE a.id_usuario = ?
                        AND b.aplicacion = ?";
                $prepare = $dbAdmin->prepare($sql);
                $resRol = $dbAdmin->execute($prepare,[$resUsr->fields[0],APLICACION]);
                if(!$resRol) throw new Exception("Error al ejecutar consulta: ".$dbAdmin->errorMsg(), 100);
                if($resRol->EOF) throw new Exception("Usuario sin rol asignado en esta aplicación", 111);
                $_SESSION["rol_id"] = $resRol->fields[0];
                $_SESSION["rol_nombre"] = $resRol->fields[1];
                if($resRol) $resRol->close();

                //-- BUSCA EL AREA ASIGNADA AL USUARIO --
                $sql = "SELECT a.id_area, b.nombre
                        FROM tbl_usuarios_area AS a
                        JOIN cat_areas AS b ON a.id_area = b.id
                        WHERE a.id_usuario = ?
                        AND b.aplicacion = ?";
                $prepare = $dbAdmin->prepare($sql);
                $resArea = $dbAdmin->execute($prepare,[$resUsr->fields[0],APLICACION]);
                if(!$resArea) throw new Exception("Error al ejecutar consulta: ".$dbAdmin->errorMsg(), 100);
                if($resArea->EOF) throw new Exception("Usuario sin área asignada en esta aplicación", 112);
                $_SESSION["area_id"] = $resArea->fields[0];
                $_SESSION["area_nombre"] = $resArea->fields[1];
                if($resArea) $resArea->close();

                //-- OBTIENE EL PRIMER MENU ASIGNADO --
                $sql = "SELECT b.directorio
                        FROM tbl_usuarios_menu AS a
                        JOIN tbl_menu AS b ON a.id_menu = b.id
                        WHERE a.id_usuario = ?
                        AND b.sistema = ?
                        AND b.status = ?
                        ORDER BY b.orden ASC
                        LIMIT 1";
                $prepare = $dbAdmin->prepare($sql);
                $resMenu = $dbAdmin->execute($prepare,[$resUsr->fields[0], APLICACION, 1]);
                if(!$resMenu) throw new Exception("Error al ejecutar consulta: ".$dbAdmin->errorMsg(), 100);
                if($resMenu->EOF) throw new Exception("Usuario sin menú asignado en esta aplicación", 113);
                $ruta = NIVEL.$resMenu->fields[0];
                if($resMenu) $resMenu->close();

                //-- OBTIENE O CREA REGISTRO CONTROL --
                $sql = "SELECT *
                        FROM tbl_control_usuarios
                        WHERE id_aplicacion = ?
                        AND id_usuario = ?";
                $prepare = $dbAdmin->prepare($sql);
                $resCtrl = $dbAdmin->execute($prepare, [APLICACION, $_SESSION["usuario_id"]]);

                //-- CREA REGISTRO DE CONTROL --
                if($resCtrl->EOF){
                    $values["id_usuario"] = $_SESSION["usuario_id"];
                    $values["id_aplicacion"] = APLICACION;
                    $values["id_rol"] = $_SESSION["rol_id"];
                    $values["id_area"] = $_SESSION["area_id"];
                    $values["ultimo_acceso"] = date("Y-m-d H:i:s");
                    $values["no_sesion"] = session_id();
                    $values["en_linea"] = 1;
                    $values["intentos"] = $values["bloqueado"] = 0;
                    $dbAdmin->autoExecute("tbl_control_usuarios",$values,"INSERT");
                }
                //-- OBTIENE INFORMACION DEL REGISTRO DE CONTROL --
                else{
                    $values["ultimo_acceso"] = date("Y-m-d H:i:s");
                    $values["en_linea"] = 1;
                    $condition = "id_usuario = ".$_SESSION["usuario_id"]." AND id_aplicacion = ".APLICACION." AND no_sesion = '".session_id()."'";
                    $dbAdmin->autoExecute("tbl_control_usuarios",$values,"UPDATE",$condition);
                }

                //-- GUARDA EN BITACORA --
                guardaBitacora(APLICACION,"Accede al Sistema", $_SESSION["usuario_nombre"], "tbl_usuarios",  $_SESSION["usuario_id"], "");

                //-- CIERRA CONEXION A LA BASE DE DATOS --

                if($resCtrl) $resCtrl->close();
                if($resUsr) $resUsr->close();
                $dbAdmin->close();

                //-- REGRESA RESULTADO --
                return array("error" => false, "msg" => "Acceso correcto", "ruta" => $ruta);
        }
        catch(Exception $e){
            $log = guardaLog(APLICACION, $e->getCode(), $e->__toString());
            return array("error" => true, "error_msg" => $log["msg"], "ruta" => "../index.php");
        }

    }






    /*
    //-- CONFIGURACION --
    include(NIVEL."../com/config.php");
    $dbAdmin = conectaBD("mysql","admin");

    //-- BUSCA INFORMACION DEL USUARIO --
    $sql = "SELECT a.id, a.nombre, a.ap_paterno, a.ap_materno, a.foto
            FROM tbl_usuarios AS a
            JOIN tbl_usuarios_aplicacion AS b ON a.id = b.id_usuario
            WHERE b.id_aplicacion = ?
            AND a.usuario = ?
            AND a.contrasena = ?
            AND a.status = 1";
    $prepare = $dbAdmin->prepare($sql);
    $resultUsuario = $dbAdmin->execute($prepare, [APLICACION, $usuario, $contrasena]);
    if($resultUsuario){

        //-- SI NO ENCONTRO EL USUARIO POR QUE SUS CREDENCIALES SON INCORRECTAS --
        if($resultUsuario->EOF){

            $_SESSION["login_error"] = 1;
            $_SESSION["login_error_msg"] = "Usuario o contraseña incorrecta!";

            //-- OBTIENE ID USUARIO --
            $params = array($usuario);
            $prepare = $dbAdmin->prepare("SELECT id FROM tbl_usuarios WHERE usuario = ?");
            $result2 = $dbAdmin->execute($prepare,$params);
            if(!$result2->EOF){
                //-- OBTIENE CONTROL DE USUARIO --
                $control = control_usuario($result2->fields[0]);

                //-- AGREGA CONTEO --
                $intentos = $control["intentos"]+1;
                if($intentos<3){
                    $values["intentos"] = $intentos;
                    $_SESSION["login_error_msg"] = "Usuario o contraseña incorrecta! $intentos intento(s)";
                }
                else{
                    // enviar correo de qcceso bloqueado
                    $values["bloqueado"] = 1;
                    $values["intentos"] = $intentos;
                    $_SESSION["login_error_msg"] = "Usuario bloqueado por mas de 3 intentos de acceso fallidos";
                }
                $condicion = "id_usuario = ".$result2->fields[0]." AND aplicacion = ".APLICACION;
                $dbAdmin->autoExecute("tbl_control_usuarios",$values,"UPDATE",$condicion);
            }

            if($result1) $result1->close();
            if($result2) $result2->close();
            $dbAdmin->close();
            header("Location: ../index.php");
            die();
        }

        //-- SI ENCONTRO EL USUARIO --
        else{
            $_SESSION["id_usuario"] = $result1->fields[0];
            $_SESSION["nombre_usuario"] = $result1->fields[1]." ".$result1->fields[2]." ".$result1->fields[3];
            $_SESSION["foto"] = (strlen($result1->fields[4])>0)? $result1->fields[4] : "default.png" ;
            $_SESSION["id_session"] = $id_session;
            $_SESSION["aplicacion"] = APLICACION;

            //-- OBTIENE CONTROL DE USUARIO --
            $control = control_usuario($result1->fields[0]);

            //-- SI EL USUARIO ESTA BLOQUEADO --
            if($control["bloqueado"]){
                $_SESSION["login_error"] = 1;
                $_SESSION["login_error_msg"] = "Usuario bloqueado por mas de 3 intentos de acceso fallidos";
                if($result1) $result1->close();
                $dbAdmin->close();
                header("Location: ../index.php");
                die();
            }

            //-- SI EL USUARIO ESTA EN LINEA --
            if($control["en_linea"] && $control["no_sesion"]!=$id_session){
                $_SESSION["login_error"] = 1;
                $_SESSION["login_error_msg"] = "Usuario con sesion abierta en otro navegador!";
                if($result1) $result1->close();
                $dbAdmin->close();
                header("Location: ../index.php");
                die();
            }

            //-- BUSCA EL ROL ASIGNADO AL USUARIO --
            $sql = "SELECT a.id_rol, b.nombre
                    FROM tbl_usuarios_rol AS a
                    JOIN cat_roles AS b ON a.id_rol = b.id
                    WHERE a.id_usuario = ?
                    AND b.aplicacion = ?";
            $prepare = $dbAdmin->prepare($sql);
            $result2 = $dbAdmin->execute($prepare,[$result1->fields[0],APLICACION]);
            if($result2->EOF){
                $_SESSION["login_error"] = 1;
                $_SESSION["login_error_msg"] = "Sin privilegios en esta aplicación";
                if($result1) $result1->close();
                if($result2) $result2->close();
                $dbAdmin->close();
                header("Location: ../index.php");
                die();
            }
            else{
                $_SESSION["id_rol"] = $result2->fields[0];
                $_SESSION["nombre_rol"] = $result2->fields[1];
            }

            //-- BUSCA EL AREA ASIGNADA AL USUARIO --
            $sql = "SELECT a.id_area, b.nombre
                    FROM tbl_usuarios_area AS a
                    JOIN cat_areas AS b ON a.id_area = b.id
                    WHERE a.id_usuario = ?
                    AND b.aplicacion = ?";
            $params = array($result1->fields[0],APLICACION);
            $prepare = $dbAdmin->prepare($sql);
            $result2 = $dbAdmin->execute($prepare,$params);
            if($result2->EOF){
                $_SESSION["id_area"] = 0;
                $_SESSION["nombre_area"] = "N/A";
            }
            else{
                $_SESSION["id_area"] = $result2->fields[0];
                $_SESSION["nombre_area"] = $result2->fields[1];
            }

            //-- GUARDA ULTIMO ACCESO --
            $values["ultimo_acceso"] = $ahora;
            $values["en_linea"] = 1;
            $values["no_sesion"] = $id_session;
            $condition = "id_usuario = ".$result1->fields[0]." AND aplicacion = ".APLICACION;
            $dbAdmin->autoExecute("tbl_control_usuarios",$values,"UPDATE",$condition);

            //-- OBTIENE EL PRIMER MENU ASIGNADO --
            $sql = "SELECT b.directorio
                    FROM tbl_usuarios_menu AS a
                    JOIN tbl_menu AS b ON a.id_menu = b.id
                    WHERE a.id_usuario = ?
                    AND b.sistema = ?
                    AND b.status = 1
                    ORDER BY b.orden ASC
                    LIMIT 1";
            $params = array($result1->fields[0],APLICACION);
            $prepare = $dbAdmin->prepare($sql);
            $result2 = $dbAdmin->execute($prepare,$params);
            $_SESSION["acceso"] = 1;
            $_SESSION["ruta"] = $result2->fields[0];
            if($result1) $result1->close();
            if($result2) $result2->close();
            $dbAdmin->close();

            header("Location: ../".$_SESSION["ruta"]);
            die();
        }
    }

    //-- SI HAY ERROR DE CONEXION --
    else{
        $_SESSION["login_error"] = 1;
        $_SESSION["login_error_msg"] = "No hay conexion a la BD";
        if($result1) $result1->close();
        $dbAdmin->close();

        die();
    }


    //-------------------------------------------------
    //-- OBTIENE INFORMACION DEL REGISTRO DE CONTROL --
    //-------------------------------------------------
    function control_usuario($id_usuario){
        global $dbAdmin;
        $control = $values = array();

        //-- OBTIENE LOS MINUTOS DE SESION ACTIVA --
        $params = array(APLICACION);
        $prepare = $dbAdmin->prepare("SELECT * FROM cat_aplicaciones WHERE id = ?");
        $result = $dbAdmin->execute($prepare,$params);
        $minutos_sesion = $result->fields["minutos_sesion"];

        //-- CIERRA SESIONES EXPIRADAS --
        $values["en_linea"] = 0;
        $condition = "aplicacion = ".APLICACION." AND ultimo_acceso <= DATE_ADD(NOW(), INTERVAL -$minutos_sesion MINUTE)";
        $dbAdmin->autoExecute("tbl_control_usuarios",$values,"UPDATE",$condition);

        //-- OBTIENE O CREA REGISTRO CONTROL --
        $sql = "SELECT *
                FROM tbl_control_usuarios
                WHERE aplicacion = ?
                AND id_usuario = ?";
        $params = array(APLICACION,$id_usuario);
        $prepare = $dbAdmin->prepare($sql);
        $result = $dbAdmin->execute($prepare,$params);
        //-- CREA REGISTRO DE CONTROL --
        if($result->EOF){
            $control["ultimo_acceso"] = $control["no_sesion"] = "";
            $control["bloqueado"] = $control["en_linea"] = false;
            $control["intentos"] = 0;
            $values["id_usuario"] = $id_usuario;
            $values["aplicacion"] = APLICACION;
            $values["en_linea"] = $values["intentos"] = $values["bloqueado"] = 0;
            $dbAdmin->autoExecute("tbl_control_usuarios",$values,"INSERT");
        }
        //-- OBTIENE INFORMACION DEL REGISTRO DE CONTROL --
        else{
            $control["ultimo_acceso"] = $result->fields["ultimo_acceso"];
            $control["intentos"] = $result->fields["intentos"];
            $control["no_sesion"] = $result->fields["no_sesion"];
            $control["bloqueado"] = ($result->fields["bloqueado"]==1)? true : false;
            $control["en_linea"] = ($result->fields["en_linea"]==1)? true : false;
        }
        return $control;
    }


    */
?>
