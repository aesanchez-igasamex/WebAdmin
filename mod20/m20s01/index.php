<?php
    //-- CONSTANTES --
    define("NIVEL","../../");
    define("MODULO","m20s01");

    //-- CONFIGURACIONES GENERALES --
    require_once(NIVEL."com/cabecera.php");

    //-- PARAMETROS Y VARIABLES --
    $status = (isset($_POST["status"])) ? $_POST["status"] : 1 ;
    $reg = $catStatus = array();

    require_once(NIVEL."../com/config.php");
    $dbAdmin = conectaBD("mysql","admin");

    //-- CONSULTA REGISTROS --
    $cont = 1;
    $sql = "SELECT a.id, a.clave, a.nombre, a.abreviatura, b.nombre, b.color, b.icono
            FROM cat_actividad_regulada AS a
            JOIN cat_status_general AS b ON a.status = b.id
            WHERE a.status = ?";
    $prepare = $dbAdmin->prepare($sql);
    $result = $dbAdmin->execute($prepare,[$status]);
    while(!$result->EOF){
        $reg[$cont]["id"] = $result->fields[0];
        $reg[$cont]["abrCNE"] = $result->fields[1];
        $reg[$cont]["nombre"] = $result->fields[2];
        $reg[$cont]["abrSAT"] = $result->fields[3];
        $reg[$cont]["status_nombre"] = $result->fields[4];
        $reg[$cont]["status_color"] = $result->fields[5];
        $reg[$cont]["status_icono"] = $result->fields[6];
        $result->MoveNext();
        $cont++;
    }

    //-- CATÁLOGO DE STATUS --
    $cont = 1;
    $prepare = $dbAdmin->prepare("SELECT * FROM cat_status_general WHERE status = ? ORDER BY nombre ASC");
    $result = $dbAdmin->execute($prepare,[1]);
    while(!$result->EOF){
        $catStatus[$cont]["id"] = $result->fields["id"];
        $catStatus[$cont]["nombre"] = $result->fields["nombre"];
        $result->MoveNext();
        $cont++;
    }

    //-- CIERRA CONEXION A LA BASE DE DATOS --
    if($result) $result->close();
    $dbAdmin->close();
?>

<!DOCTYPE html>
<html lang="es">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="shortcut icon" href="assets/images/icono.ico">
        <title><?= TITULO ?></title>

        <?php include(NIVEL."com/dependenciesUP.php"); ?>
        <script type="text/javascript">
            $(document).ready(function(){
                <?php if($menuINFO["menu_tipo"]==1){ ?>
                    $("#<?= $menuINFO["menu_identificador"]?>").addClass("active-page-link");
                <?php } ?>

                <?php if($menuINFO["menu_tipo"]==2){ ?>
                    $("#<?= $menuINFO["menu_identificador"]?>").addClass("active-page-link");
                    $("#<?= $menuINFO["menu_identificador"]?>").addClass("active");
                    $("#<?= $menuINFO["submenu_identificador"]?>").addClass("active-page-link");
                <?php } ?>

                $("#status option:selected").removeAttr("selected");
                $("#status option[value='<?= $status; ?>']").attr('selected', 'selected');

                $("#status").change(function(){ $(window.parent.document.body).loadingModal({ text: 'Buscando ...', animation: 'cubeGrid' }); $("#formBusqueda").submit(); });

                $("#tabla").DataTable({
                    "order": [[ 0, "asc" ]],
                    "language": {
                        "lengthMenu": "Mostrar _MENU_ registros por pagina",
                        "info": "Mostrando pagina _PAGE_ de _PAGES_",
                        "search": "Buscar:"
                    },
                });

                $(".form-select-chosen").trigger("change.select2");

            });

            function registro(id){
                $("#modalRegistro iframe").attr("src","editarRegistro.php?id="+id);
                $("#modalRegistro").modal("show");
            }

            function tamano(alto, modal){
                $("#"+modal+" iframe").removeAttr("height");
                $("#"+modal+" iframe").attr("height",alto);
            }

            function recargar(){
                location.reload();
            }

        </script>
    </head>

    <body>

        <!-- GENERAL -->
        <div class="page-wrapper">

            <!-- CABECERA -->
            <div class="page-header">

                <!-- LOGO -->
                <div class="brand">
                    <a href="#" class="logo">
                        <img src="<?= NIVEL ?>../vendor/assetsB/images/igasamex.png" class="d-none d-md-block me-4" alt="Admin Dashboards">
                        <img src="<?= NIVEL ?>../vendor/assetsB/images/igasamex.png" class="d-block d-md-none me-4" alt="Admin Dashboards">
                    </a>
                </div>

                <!-- TOGGLE -->
                <div class="toggle-sidebar" id="toggle-sidebar">
                    <i class="bi bi-list"></i>
                </div>

                <!-- CONTENEDOR DE ACCIONES -->
                <div class="header-actions-container">

                    <!-- BUSQUEDA --
                    <div class="search-container me-4 d-xl-block d-lg-none">
                        <div class="input-group">
                            <input type="text" class="form-control" id="searchAny" placeholder="Search">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                    -->

                    <!-- MENSAJES -->
                    <?= $generalMensajes ?>


                    <!-- PERFIL -->
                    <?= $generalPerfil ?>

                </div>
                <!-- CONTENEDOR DE ACCIONES FIN -->

            </div>
            <!-- CABECERA FIN -->


            <!-- PRINCIPAL -->
            <div class="main-container">

                <!-- LATERAL -->
                <nav class="sidebar-wrapper">

                    <!-- MENU SUPERIOR -->
                    <?= $menuSuperior ?>

                    <!-- MENU INFERIOR -->
                    <?= $menuInferior ?>

                </nav>
                <!-- LATERAL FIN -->

                <!-- CONTENIDO -->
                <div class="content-wrapper-scroll">

                    <!-- SUPERIOR -->
                    <div class="main-header d-flex align-items-center justify-content-between position-relative">
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="page-icon pe-3">
                                <?= $menuINFO["menu_icono"] ?>
                            </div>
                            <div class="page-title d-none d-md-block">
                                <h5><?= $menuINFO["menu_nombre"] ?></h5>
                            </div>
                        </div>

                        <!-- NOTIFICACIONES -->
                        <?= $generalNotificaciones ?>

                    </div>
                    <!-- SUPERIOR FIN -->

                    <!-- INFERIOR -->
                    <div class="content-wrapper">

                        <!-- BUSQUEDA -->
                        <div class="row gx-3">
                            <div class="col-sm-12">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <div class="row">
                                            <div class="col-10">
                                                <h5 class="card-title">Busqueda</h5>
                                            </div>
                                            <div class="col-2  d-flex align-items-end flex-column ">
                                                <button type="button" class="btn btn-sm btn-primary" onclick="registro(0);">
                                                    <i class="bi bi-window-plus"></i> Agregar
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-body">
                                        <form id="formBusqueda" method="post" action="index.php">
                                            <div class="row">
                                                <div class="col-md-5"></div>
                                                <div class="col-md-2">
                                                    <label for="status" class="form-label pt-1">Status</label>
                                                    <select class="form-select form-select-sm form-select-chosen shadow-sm" id="status" name="status">
                                                        <?php for($x=1;$x<=count($catStatus);$x++){ ?>
                                                            <option value="<?= $catStatus[$x]["id"] ?>"><?= $catStatus[$x]["nombre"] ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-5"></div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TABLA -->
                        <div class="row gx-3">
                            <div class="col-sm-12 col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table id="tabla" class="table custom-table table-hover">
                                                <thead>
                                                    <tr class="small text-center">
                                                        <th width="8%">Id</th>
                                                        <th width="17%">Abreviación CNE</th>
                                                        <th width="17%">Abreviación SAT</th>
                                                        <th width="30%">Nombre</th>
                                                        <th width="14%">Estatus</th>
                                                        <th width="14%">Accion</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php for($x=1;$x<=count($reg);$x++){ ?>
                                                        <tr class="small text-center">
                                                            <td><?= $reg[$x]["id"] ?></td>
                                                            <td><?= $reg[$x]["abrCNE"] ?></td>
                                                            <td><?= $reg[$x]["abrSAT"] ?></td>
                                                            <td class="text-start pl-3"><?= $reg[$x]["nombre"] ?></td>
                                                            <td class="<?= $reg[$x]["status_color"] ?>"><?= $reg[$x]["status_icono"]." ".$reg[$x]["status_nombre"] ?></td>
                                                            <td>
                                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="registro('<?= $reg[$x]['id'] ?>');" data-bs-toggle="tooltip" title="Editar Registro">
                                                                    <i class="bi bi-pencil-square"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- INFERIOR FIN -->

                </div>
                <!-- CONTENIDO FIN -->

                <div class="app-footer">
                    <span><?= COPYRIGHT ?></span>
                </div>

            </div>
            <!-- PRINCIPAL FIN -->

        </div>
        <!-- GENERAL FIN -->

        <!-- MODAL REGISTRO -->
        <div class="modal fade" id="modalRegistro" tabindex="-1" aria-labelledby="modalRegistroTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalRegistroCenterTitle">
                            <i class="bi bi-journal-check"></i> Actividad Regulada
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-2">
                        <iframe width="100%" frameborder="0" style="border:none;"></iframe>
                    </div>
                </div>
            </div>
        </div>

        <?= $generalModales ?>

        <?php include(NIVEL."com/dependenciesDown.php"); ?>

    </body>

</html>