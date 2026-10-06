<?php
    //-- CONSTANTES --
    define("NIVEL","../../");
    define("MODULO","m10s02");

    //-- CONFIGURACIONES GENERALES --
    require_once(NIVEL."com/cabecera.php");

    //-- PARAMETROS Y VARIABLES --
    $id_actividad_regulada = (is_null($_POST["id_actividad_regulada"]) || $_POST["id_actividad_regulada"] === "ALL") ? null : $_POST["id_actividad_regulada"] ;
    $id_comercializador = (is_null($_POST["id_comercializador"]) || $_POST["id_comercializador"] === "ALL") ? null : $_POST["id_comercializador"] ;
    $id_status = (is_null($_POST["id_status"]) || $_POST["id_status"] === "ALL") ? null : $_POST["id_status"] ;
    $param = $reg = $catActividadRegulada = $catStatus = array();

    require_once(NIVEL."../com/config.php");
    $dbAdmin = conectaBD("mysql","admin");

    //-- CONSULTA REGISTROS --
    $cont = 1;
    $sql = "SELECT a.id, a.permiso, a.rfc, a.razon_social, b.nombre, a.preciso, a.operacion_ini, a.operacion_fin, c.nombre, c.color, c.icono
            FROM tbl_permisionarios AS a
            JOIN cat_sociedad_mercantil AS b ON a.id_sociedad_mercantil = b.id
            JOIN cat_status_general AS c ON a.status = c.id
            WHERE a.id > 0 ";
    if($id_actividad_regulada!==null){ $sql .= " AND a.id_actividad_regulada = ?"; $param[] = $id_actividad_regulada; }
    if($id_comercializador!==null){ $sql .= " AND a.id_comercializadora = ?"; $param[] = $id_comercializador; }
    if($id_status!==null){ $sql .= " AND a.status = ?"; $param[] = $id_status; }
    $prepare = $dbAdmin->prepare($sql);
    $result = $dbAdmin->execute($prepare,$param);
    while(!$result->EOF){
        $reg[$cont]["id"] = $result->fields[0];
        $reg[$cont]["permiso"] = $result->fields[1];
        $reg[$cont]["rfc"] = $result->fields[2];
        $reg[$cont]["nombre"] = (strlen($result->fields[5])>0)? $result->fields[3] . " (" . $result->fields[5] . ")" : $result->fields[3] ;
        $reg[$cont]["soc_mercantil"] = $result->fields[4];
        $reg[$cont]["operacion_ini"] = (isset($result->fields[6]) && $result->fields[6]!="0000-00-00") ? $result->fields[6] : "-";
        $reg[$cont]["operacion_fin"] = (isset($result->fields[7]) && $result->fields[7]!="0000-00-00") ? $result->fields[7] : "-";
        $reg[$cont]["status_nombre"] = $result->fields[8];
        $reg[$cont]["status_color"] = $result->fields[9];
        $reg[$cont]["status_icono"] = $result->fields[10];
        $result->MoveNext();
        $cont++;
    }

    //-- CATÁLOGO DE ACTIVIDAD REGULADA --
    $cont = 1;
    $prepare = $dbAdmin->prepare("SELECT * FROM cat_actividad_regulada WHERE status = ? ORDER BY nombre ASC");
    $result = $dbAdmin->execute($prepare,[1]);
    while(!$result->EOF){
        $catActividadRegulada[$cont]["id"] = $result->fields["id"];
        $catActividadRegulada[$cont]["nombre"] =  $result->fields["clave"] . " - " . $result->fields["nombre"];
        $result->MoveNext();
        $cont++;
    }

    //-- CATÁLOGO DE COMERCIALIZADORAS --
    $cont = 1;
    $prepare = $dbAdmin->prepare("SELECT * FROM tbl_permisionarios WHERE id_actividad_regulada = ? AND status = ? ORDER BY id ASC");
    $result = $dbAdmin->execute($prepare,[1,1]);
    while(!$result->EOF){
        $catComercializadora[$cont]["id"] = $result->fields["id"];
        $catComercializadora[$cont]["nombre"] = $result->fields["razon_social"];
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

                <?php if($id_actividad_regulada!==null){ ?>
                    $("#id_actividad_regulada option:selected").removeAttr("selected");
                    $("#id_actividad_regulada option[value='<?= $id_actividad_regulada; ?>']").attr('selected', 'selected');
                <?php } ?>
                <?php if($id_comercializador!==null){ ?>
                    $("#id_comercializador option:selected").removeAttr("selected");
                    $("#id_comercializador option[value='<?= $id_comercializador; ?>']").attr('selected', 'selected');
                <?php } ?>
                <?php if($id_status!==null){ ?>
                    $("#id_status option:selected").removeAttr("selected");
                    $("#id_status option[value='<?= $id_status; ?>']").attr('selected', 'selected');
                <?php } ?>

                $("#id_actividad_regulada, #id_comercializador, #id_status").change(function(){ $(window.parent.document.body).loadingModal({ text: 'Buscando ...', animation: 'cubeGrid' }); $("#formBusqueda").submit(); });

                $("#tabla").DataTable({
                    "order": [[ 0, "asc" ]],
                    "language": {
                        "lengthMenu": "Mostrar _MENU_ registros por pagina",
                        "info": "Pagina _PAGE_ de _PAGES_, en total _MAX_ registros.",
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
                                                <div class="col-md-3">
                                                    <label for="id_actividad_regulada" class="form-label pt-1">Actividad Regulada</label>
                                                    <select class="form-select form-select-sm form-select-chosen shadow-sm" id="id_actividad_regulada" name="id_actividad_regulada">
                                                        <option value="ALL">Todas</option>
                                                        <?php for($x=1;$x<=count($catActividadRegulada);$x++){ ?>
                                                            <option value="<?= $catActividadRegulada[$x]["id"] ?>"><?= $catActividadRegulada[$x]["nombre"] ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="id_comercializador" class="form-label pt-1">Comercializadora</label>
                                                    <select class="form-select form-select-sm form-select-chosen shadow-sm" id="id_comercializador" name="id_comercializador">
                                                        <option value="ALL">Todas</option>
                                                        <?php for($x=1;$x<=count($catComercializadora);$x++){ ?>
                                                            <option value="<?= $catComercializadora[$x]["id"] ?>"><?= $catComercializadora[$x]["nombre"] ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>

                                                <div class="col-md-2">
                                                    <label for="id_status" class="form-label pt-1">Status</label>
                                                    <select class="form-select form-select-sm form-select-chosen shadow-sm" id="id_status" name="id_status">
                                                        <option value="ALL">Todos</option>
                                                        <?php for($x=1;$x<=count($catStatus);$x++){ ?>
                                                            <option value="<?= $catStatus[$x]["id"] ?>"><?= $catStatus[$x]["nombre"] ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
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
                                                        <th width="6%">Id</th>
                                                        <th width="20%">Nombre</th>
                                                        <th width="12%">Soc. Mercantil</th>
                                                        <th width="12%">Permiso</th>
                                                        <th width="10%">RFC</th>
                                                        <th width="12%">Inicio Operación</th>
                                                        <th width="12%">Fin Operación</th>
                                                        <th width="10%">Estatus</th>
                                                        <th width="6%">Accion</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php for($x=1;$x<=count($reg);$x++){ ?>
                                                        <tr class="small text-center">
                                                            <td><?= $reg[$x]["id"] ?></td>
                                                            <td class="text-start pl-3"><?= $reg[$x]["nombre"] ?></td>
                                                            <td class="text-start pl-3"><?= $reg[$x]["soc_mercantil"] ?></td>
                                                            <td><?= $reg[$x]["permiso"] ?></td>
                                                            <td><?= $reg[$x]["rfc"] ?></td>
                                                            <td><?= $reg[$x]["operacion_ini"] ?></td>
                                                            <td><?= $reg[$x]["operacion_fin"] ?></td>
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
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalRegistroCenterTitle">
                            <i class="bi bi-journal-check"></i> Información del permisionario
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