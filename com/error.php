<?php
    //-- CONSTANTES --
    define("NIVEL","../");
?>

<!DOCTYPE html>
<html lang="es">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="shortcut icon" href="<?= NIVEL ?>../vendor/assetsB/images/icono.ico">
        <title>Sin Privilegios</title>

        <link rel="stylesheet" href="<?= NIVEL ?>../vendor/assetsB/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?= NIVEL ?>../vendor/assetsB/css/login.min.css">
        <link rel="stylesheet" href="<?= NIVEL ?>../vendor/assetsB/vendor/particles/particles.css">
    </head>

    <body class="error-screen">
        <div id="particles-js"></div>
        <div class="countdown-bg"></div>

        <div class="d-flex flex-column position-relative text-center p-5 mt-5">
            <h1>404</h1>
            <h5 class="fw-lighter mb-4">
                Ruta no valida.
            </h5>
            <a href="../" class="btn m-auto fw-bold">Regresa al Inicio</a>
        </div>

        <!-- Required jQuery first, then Bootstrap Bundle JS -->
        <script src="<?= NIVEL ?>../vendor/assetsB/js/jquery.min.js"></script>
        <script src="<?= NIVEL ?>../vendor/assetsB/js/bootstrap.bundle.min.js"></script>
        <script src="<?= NIVEL ?>../vendor/assetsB/js/moment.js"></script>

        <!-- Particles JS -->
        <script src="<?= NIVEL ?>../vendor/assetsB/vendor/particles/particles.min.js"></script>
        <script src="<?= NIVEL ?>../vendor/assetsB/vendor/particles/particles-custom.js"></script>
    </body>

</html>