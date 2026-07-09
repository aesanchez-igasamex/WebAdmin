<?php
    //-- CONSTANTES --
    define("NIVEL","");

    //-- ABRE SESION --
    session_start();

    //-- PARAMETROS Y VARIABLES --
    $id_session = session_id();

    include(NIVEL."../com/config.php");
    actualizaSesiones();

    $error = ($_SESSION["login_error"] == 1)? true : false ;
    $error_msg = $_SESSION["login_error_msg"];
?>


<!DOCTYPE html>
<html lang="es">

    <head>
        <!-- <meta http-equiv="Content-Security-Policy" content="script-src * 'unsafe-inline' 'unsafe-eval';">-->

        <meta charset="utf-8">

        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Sistema de Administracion de Aplicaciones</title>

        <!-- CSS -->
        <!-- <link rel="canonical" href="https://www.bootstrap.gallery/"> -->
        <link rel="shortcut icon" href="<?= NIVEL ?>../vendor/assetsB/images/favicon.svg">
        <link rel="stylesheet" href="<?= NIVEL ?>../vendor/assetsB/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?= NIVEL ?>../vendor/assetsB/fonts/bootstrap-icons.min.css">
        <link rel="stylesheet" href="<?= NIVEL ?>../vendor/assetsB/css/main.min.css">
        <link rel="stylesheet" href="<?= NIVEL ?>../vendor/assetsB/css/login.min.css">
        <style>
            body {
                /* Establece la imagen de fondo */
                background-image: url('img/wallpaper.png') !important;

                /* Hace que la imagen no se mueva al hacer scroll */
                background-attachment: fixed !important;

                /* Centra la imagen */
                background-position: center !important;

                /* Evita que la imagen se repita */
                background-repeat: no-repeat !important;

                /* Escala la imagen para cubrir toda el área sin deformarse */
                background-size: cover !important;

                /* Opcional: Color de fondo de respaldo mientras carga la imagen */
                background-color: #000;
            }
        </style>

        <!-- JAVASCRIPT -->
        <script src="<?= NIVEL ?>../vendor/assetsB/js/jquery.min.js"></script>
        <script type="text/javascript">
            $(document).ready(function(){
                <?= ($error)? "$(\"#error\").show();" : "$(\"#error\").hide();" ; ?>
                $("#alertUser").hide();
                $("#alertPass").hide();
                $("#usuario").focus();

                //-- MANDA A VALIDAR CAMPOS AL PRESIONAR EL BOTON --
                $("#acceder").click(function(){ validaForm(); });

                //-- MANDA A VALIDAR CAMPOS CON TECLA ENTER --
                $(document).keypress(function(e) { if(e.which == 13) { validaForm(); } });
            });

            //-- FUNCION PARA VALIDAR CAMPOS --
            function validaForm(){
                var formOK = true;
                if( $("#usuario").val() == "" ){
                    formOK = false;
                    $("#error").hide();
                    $("#alertPass").hide();
                    $("#alertUser").show();
                    $("#usuario").focus();
                }
                if( $("#contrasena").val() == "" && formOK){
                    formOK = false;
                    $("#error").hide();
                    $("#alertUser").hide();
                    $("#alertPass").show();
                    $("#contrasena").focus();
                }
                if( formOK ){ $("#autentificacion").submit(); }
            }
        </script>
    </head>

    <body class="login-container">

        <div class="container d-flex justify-content-center align-items-center vh-100">
            <form id="autentificacion" method="post" action="com/valida.php" autocomplete="off">
            <div class="login-box rounded-2 p-4 mt-4">
                <div class="login-form">
                <div class="text-center">
                    <h5 class="fw-light mb-4">
                    Administración
                    <img src="<?= NIVEL ?>../vendor/assetsB/images/logo.png" width="90%">
                    </h5>


                </div>


                <div class="mb-3">
                    <label class="form-label" for="usuario">Usuario</label>
                    <input type="text" class="form-control" name="usuario" id="usuario" placeholder="Escribe tu usuario">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="yPwd">Contraseña</label>
                    <input type="password" class="form-control" name="contrasena" id="contrasena" placeholder="Escribe tu contraseña">
                </div>
                <div class="form-groups row" id="error">
                    <div class="col-sm-12">
                    <div class="alert alert-danger alert-dismissible pt-2 pb-2">
                        <button type="button" class="btn-close pb-1" onClick="$('#error').hide();"></button>

                        <?php if($error) echo $error_msg; ?>
                    </div>
                    </div>
                </div>
                <div class="form-groups row" id="alertUser">
                    <div class="col-sm-12">
                    <div class="alert alert-warning alert-dismissible pt-2 pb-2">
                        <button type="button" class="btn-close pb-1" onClick="$('#alertUser').hide();"></button>
                         Introduzca su Usuario!
                    </div>
                    </div>
                </div>
                <div class="form-groups row" id="alertPass">
                    <div class="col-sm-12">
                    <div class="alert alert-warning alert-dismissible pt-2 pb-2">
                        <button type="button" class="btn-close pb-1" onClick="$('#alertPass').hide();"></button>
                         Introduzca su Contraseña!
                    </div>
                    </div>
                </div>

                <div class="d-grid py-3">
                    <button type="button" class="btn btn-lg btn-primary" id="acceder"> <i class="bi bi-door-closed-fill"></i> Acceder </button>
                </div>

                </div>
            </div>
            </form>
        </div>

    </body>

</html>