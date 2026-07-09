<?php
    //-- CONSTANTES --
    define("NIVEL","../../");

    //-- PARAMETROS Y VARIABLES --
    $id = $_GET["id"];

    //-- CONECTA A LA BASE DE DATOS --
    require_once(NIVEL."../com/config.php");
    $dbAdmin = conectaBD("mysql","admin");

    if($id>0){
        $prepare = $dbAdmin->prepare("SELECT * FROM tbl_historial WHERE id = ?");
        $result = $dbAdmin->execute($prepare,array($id));
        $reg["fecha"] = $result->fields["fecha"];
        $reg["realizo"] = $result->fields["realizo"];
        $reg["actividad"] = $result->fields["actividad"];
        $reg["tabla"] = $result->fields["tabla"];
        $reg["registro"] = $result->fields["registro"];
        $reg["observacion"] = (strlen($result->fields["observacion"])>0)? $result->fields["observacion"] : "-" ;
    }

    //-- CIERRA CONEXION A LA BASE DE DATOS --
    if ($result) $result->close();
    $dbAdmin->close();
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Editar registro</title>
        <?php include(NIVEL."com/dependenciesUP.php"); ?>
        <style> body { overflow-x: hidden; } </style>
        <script type="text/javascript">
            $(document).ready(function(){  window.parent.tamano(225); });
        </script>
    </head>

    <body>
        <div class="row gx-3">
            <div class="col-xxl-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5">Fecha: <div class="text-secondary"><?= $reg["fecha"] ?></div></div>
                            <div class="col-7">Realizo: <div class="text-secondary"><?= $reg["realizo"] ?></div></div>
                        </div>
                        <div class="row pt-2">
                            <div class="col-12">Actividad: <div class="text-secondary"><?= $reg["actividad"] ?></div></div>
                        </div>
                        <div class="row pt-2">
                            <div class="col-12">Observacion: <div class="text-secondary"><?= $reg["observacion"] ?></div></div>
                        </div>
                        <div class="row pt-2">
                            <div class="col-12">Registro: <span class="text-secondary"><?= $reg["tabla"]." / ".$reg["registro"] ?></span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
<body>