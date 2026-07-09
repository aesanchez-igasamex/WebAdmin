<?php
    //-- CONSTANTES --
    define("NIVEL","../../");
    define("MODULO","m30s02");

    //-- CONFIGURACIONES GENERALES --
    require_once(NIVEL."com/cabecera.php");

    //-- PARAMETROS Y VARIABLES --
    $hoy = date("Y-m-d");
    $id_aplicacion = (isset($_POST["id_aplicacion"])) ? $_POST["id_aplicacion"] : 0;
    $fecha_ini = (isset($_POST["fecha_ini"])) ? $_POST["fecha_ini"] : date("Y-m-d",strtotime($hoy." - 1 month")) ;
    $fecha_fin = (isset($_POST["fecha_fin"])) ? $_POST["fecha_fin"] : $hoy ;
    $reg = array();

    //-- CONECTA A LA BASE DE DATOS --
    $dbAdmin = conectaBD("mysql","admin");

    //-- CONSULTA REGISTROS --
    $cont = 1;
    $sql = "SELECT a.id, a.fecha, a.realiza, a.origen, a.error, b.nombre
            FROM tbl_log AS a
            JOIN cat_aplicaciones AS b ON a.id_aplicacion = b.id
            WHERE a.id > 0";
    if($id_aplicacion>0) $sql .= " AND a.id_aplicacion = ?";
    $sql .= " ORDER BY a.fecha DESC";
    $prepare = $dbAdmin->prepare($sql);
    $params = array();
    if($id_aplicacion>0) $params[] = $id_aplicacion;
    $result = $dbAdmin->execute($prepare,$params);
    while(!$result->EOF){
        $reg[$cont]["id"] = $result->fields[0];
        $reg[$cont]["fecha"] = date("Y-m-d H:i",strtotime($result->fields[1]))." Hrs";
        $reg[$cont]["realizo"] = $result->fields[2];
        $reg[$cont]["origen"] = $result->fields[3];
        $reg[$cont]["error"] = $result->fields[4];
        $reg[$cont]["aplicacion"] = $result->fields[5];
        $result->MoveNext();
        $cont++;
    }

    //-- OBTIENE APLICACIONES --
    $cont = 1;
    $sql = "SELECT id, nombre FROM cat_aplicaciones WHERE status = ? ORDER BY nombre";
    $prepare = $dbAdmin->prepare($sql);
    $result = $dbAdmin->execute($prepare,[1]);
    while(!$result->EOF){
        $cat_aplicaciones[$cont]["id"] = $result->fields[0];
        $cat_aplicaciones[$cont]["nombre"] = $result->fields[1];
        $cat_aplicaciones[$cont]["seleccionado"] = ($result->fields[0]==$id_aplicacion) ? "selected" : "" ;
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
        <link rel="shortcut icon" href="assets/images/favicon.svg">
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

                <?php if($id_aplicacion>0){ ?>
                    $("#id_aplicacion option:selected").removeAttr("selected");
                    $("#id_aplicacion option[value='<?= $id_aplicacion; ?>']").attr('selected', 'selected');
                    $("#id_aplicacion").css("background-color", "#cdffc9");
                <?php } ?>
                $("#fecha_ini").css("background-color", "#cdffc9");
                $("#fecha_fin").css("background-color", "#cdffc9");

                $("#id_aplicacion").change(function(){ $(window.parent.document.body).loadingModal({ text: 'Buscando...', animation: 'cubeGrid' }); $("#formBusqueda").submit(); });
                $("#fecha_ini").change(function(){ $(window.parent.document.body).loadingModal({ text: 'Buscando...', animation: 'cubeGrid' }); $("#formBusqueda").submit(); });
                $("#fecha_fin").change(function(){ $(window.parent.document.body).loadingModal({ text: 'Buscando...', animation: 'cubeGrid' }); $("#formBusqueda").submit(); });

                $('#apiCallbacks').DataTable({
                    "lengthMenu": [[10, 25, 50], [10, 25, 50, "All"]],
                    "order": [[0, "desc"]],
                    "language": {
                        "lengthMenu": "Mostrar _MENU_ registros por página",
                        "info": "Mostrando página _PAGE_ de _PAGES_",
                        "search": "Buscar:"
                    }
                });
            });

            function detalle(id){
                $("#modalDetalle iframe").attr("src","detalle.php?id="+id);
                $("#modalDetalle").modal("show");
            }

            function tamano(alto){
                $("#modalDetalle iframe").removeAttr("height");
                $("#modalDetalle iframe").attr("height",alto);
            }

            function recargar(){
                $("#modalDetalle").modal("hide");
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
                                    <div class="card-header row">
                                        <div class="col-10">
                                            <h5 class="card-title">Busqueda</h5>
                                        </div>
                                        <div class="col-2  d-flex align-items-end flex-column"></div>
                                    </div>
                                    <form id="formBusqueda" method="post" action="index.php">
                                        <div class="card-body row">
                                            <div class="col-md-1"></div>
                                            <div class="col-md-1 form-label text-end pt-1">Aplicacion</div>
                                            <div class="col-md-3">
                                                <select class="form-select" name="id_aplicacion" id="id_aplicacion">
                                                    <option value="0">Todas</option>
                                                    <?php for($i=1;$i<=count($cat_aplicaciones);$i++){ ?>
                                                        <option value="<?= $cat_aplicaciones[$i]["id"] ?>" <?= $cat_aplicaciones[$i]["seleccionado"] ?>><?= $cat_aplicaciones[$i]["nombre"] ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="col-md-1 form-label text-end pt-1">Inicio</div>
                                            <div class="col-md-2">
                                                <input type="date" class="form-control " name="fecha_ini" id="fecha_ini" value="<?= $fecha_ini ?>">
                                            </div>
                                            <div class="col-md-1 form-label text-end pt-1">Fin</div>
                                            <div class="col-md-2">
                                                <input type="date" class="form-control " name="fecha_fin" id="fecha_fin" value="<?= $fecha_fin ?>">
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- TABLA -->
                        <div class="row gx-3">
                            <div class="col-sm-12 col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table id="apiCallbacks" class="table custom-table table-hover">
                                                <thead>
                                                    <tr class="small text-center">
                                                        <th width="15%">Fecha</th>
                                                        <th width="15%">Aplicacion</th>
                                                        <th width="15%">Realizo</th>
                                                        <th width="15%">Origen</th>
                                                        <th width="30%">Error</th>
                                                        <th width="10%">Accion</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php for($x=1;$x<=count($reg);$x++){ ?>
                                                        <tr class="small text-center">
                                                            <td><?= $reg[$x]["fecha"] ?></td>
                                                            <td><?= $reg[$x]["aplicacion"] ?></td>
                                                            <td><?= $reg[$x]["realizo"] ?></td>
                                                            <td><?= $reg[$x]["origen"] ?></td>
                                                            <td><?= $reg[$x]["error"] ?></td>
                                                            <td>
                                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="detalle('<?= $reg[$x]['id'] ?>');" data-bs-toggle="tooltip" title="Detalle">
                                                                    <i class="bi bi-eye"></i>
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

        <!-- MODAL DETALLE -->
        <div class="modal fade" id="modalDetalle" tabindex="-1" aria-labelledby="modalDetalleTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalDetalleCenterTitle">
                            <i class="bi bi-window"></i> Detalle
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