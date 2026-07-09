<?php
    //-- CONSTANTES --
    define("NIVEL","../../");
    define("MODULO","m10s03");

    //-- CONFIGURACIONES GENERALES --
    require_once(NIVEL."com/cabecera.php");

    //-- PARAMETROS Y VARIABLES --
    $id_aplicacion = (isset($_REQUEST["id_aplicacion"])) ? $_REQUEST["id_aplicacion"] : 0 ;
    $status = (isset($_POST["status"])) ? $_POST["status"] : 1 ;
    $usuarios = $catAplicaciones = $catStatus = array();

    require_once(NIVEL."../com/config.php");
    $dbAdmin = conectaBD("mysql","admin");

    //-- OBTIENE REGISTROS DE TODOS LOS USUARIOS --
    if($id_aplicacion == 0){
        $cont = 1;
        $sql = "SELECT a.id, a.nombre, a.ap_paterno, a.ap_materno, b.nombre, b.estilo, b.icono
                FROM tbl_usuarios AS a
                JOIN cat_status_usuario AS b ON a.status = b.id
                WHERE a.status = ?";
        $prepare = $dbAdmin->prepare($sql);
        $result = $dbAdmin->execute($prepare,[$status]);
        while(!$result->EOF){
            $usuarios[$cont]["id"] = $result->fields[0];
            $usuarios[$cont]["nombre"] = $result->fields[1];
            $usuarios[$cont]["ap_paterno"] = $result->fields[2];
            $usuarios[$cont]["ap_materno"] = $result->fields[3];
            $usuarios[$cont]["status_nombre"] = $result->fields[4];
            $usuarios[$cont]["status_color"] = $result->fields[5];
            $usuarios[$cont]["status_icono"] = $result->fields[6];
            $usuarios[$cont]["rol"] = " - ";
            $usuarios[$cont]["area"] = " - ";
            $result->MoveNext();
            $cont++;
        }
    }


    //-- OBTIENE REGISTROS DE TODOS LOS USUARIOS DE LA APLICACION --
    if($id_aplicacion>0){
        $cont = 1;
        $sql = "SELECT b.id, b.nombre, b.ap_paterno, b.ap_materno, c.nombre, c.estilo, c.icono
                FROM tbl_usuarios_aplicacion AS a
                JOIN tbl_usuarios AS b ON a.id_usuario = b.id
                JOIN cat_status_usuario AS c ON b.status = c.id
                WHERE a.id_aplicacion = ?
                AND b.status = ?";
        $prepare = $dbAdmin->prepare($sql);
        $result = $dbAdmin->execute($prepare,[$id_aplicacion,$status]);
        while(!$result->EOF){
            $usuarios[$cont]["id"] = $result->fields[0];
            $usuarios[$cont]["nombre"] = $result->fields[1];
            $usuarios[$cont]["ap_paterno"] = $result->fields[2];
            $usuarios[$cont]["ap_materno"] = $result->fields[3];
            $usuarios[$cont]["status_nombre"] = $result->fields[4];
            $usuarios[$cont]["status_color"] = $result->fields[5];
            $usuarios[$cont]["status_icono"] = $result->fields[6];

            //-- OBTIEN ROL --
            $sql = "SELECT b.nombre
                    FROM tbl_usuarios_rol AS a
                    JOIN cat_roles AS b ON a.id_rol = b.id
                    AND b.aplicacion = ?
                    AND a.id_usuario = ?";
            $prepare = $dbAdmin->prepare($sql);
            $resultRol = $dbAdmin->execute($prepare,[$id_aplicacion,$usuarios[$cont]["id"]]);
            $usuarios[$cont]["rol"] = ($resultRol->EOF) ? "<span class=\"text-warning\"><i class=\"bi bi-exclamation-triangle-fill\"></i> Sin asignar</span>" : $resultRol->fields[0];

            //-- OBTIEN AREA --
            $sql = "SELECT b.nombre
                    FROM tbl_usuarios_area AS a
                    JOIN cat_areas AS b ON a.id_area = b.id
                    AND b.aplicacion = ?
                    AND a.id_usuario = ?";
            $prepare = $dbAdmin->prepare($sql);
            $resultArea = $dbAdmin->execute($prepare,[$id_aplicacion,$usuarios[$cont]["id"]]);
            $usuarios[$cont]["area"] = ($resultArea->EOF) ? "<span class=\"text-warning\"><i class=\"bi bi-exclamation-triangle-fill\"></i> Sin asignar</span>" : $resultArea->fields[0];

            $result->MoveNext();
            $cont++;
        }
    }

    //-- OBTIENE APLICACIONES --
    $cont = 1;
    $sql = "SELECT id, nombre, descripcion FROM cat_aplicaciones WHERE status = 1 ORDER BY id ASC";
    $prepare = $dbAdmin->prepare($sql);
    $result = $dbAdmin->execute($prepare);
    while(!$result->EOF){
        $catAplicaciones[$cont]["id"] = $result->fields[0];
        $catAplicaciones[$cont]["nombre"] = $result->fields[1]." - ".$result->fields[2];
        $result->MoveNext();
        $cont++;
    }

    //-- CATÁLOGO DE STATUS --
    $cont = 1;
    $prepare = $dbAdmin->prepare("SELECT * FROM cat_status_usuario WHERE status = ?");
    $result = $dbAdmin->execute($prepare,[1]);
    while(!$result->EOF){
        $catStatus[$cont]["id"] = $result->fields["id"];
        $catStatus[$cont]["nombre"] = $result->fields["nombre"];
        $catStatus[$cont]["color"] = $result->fields["estilo"];
        $catStatus[$cont]["icono"] = $result->fields["icono"];
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

                <?php if($id_aplicacion != 0){ ?>
                    $("#id_aplicacion option:selected").removeAttr("selected");
                    $("#id_aplicacion option[value='<?= $id_aplicacion; ?>']").attr('selected', 'selected');
                <?php } ?>
                $("#status option:selected").removeAttr("selected");
                $("#status option[value='<?= $status; ?>']").attr('selected', 'selected');

                $("#id_aplicacion").change(function(){ $(window.parent.document.body).loadingModal({ text: 'Buscando ...', animation: 'cubeGrid' }); $("#formBusqueda").submit(); });
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

            function usuario(id){
                $("#modalUsuario iframe").attr("src","editarUsuario.php?id="+id+"&id_aplicacion=<?= $id_aplicacion ?>");
                $("#modalUsuario").modal("show");
            }

            function asignar(){
                $("#modalAsignarUsuarios iframe").attr("src","asignarUsuarios.php?id_aplicacion=<?= $id_aplicacion ?>");
                $("#modalAsignarUsuarios").modal("show");
            }

            function tamano(alto, modal){
                $("#" + modal + " iframe").removeAttr("height");
                $("#" + modal + " iframe").attr("height",alto);
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
                                            <div class="col-8">
                                                <h5 class="card-title">Busqueda</h5>
                                            </div>
                                            <div class="col-4 align-items-end d-flex justify-content-end">
                                                <?php if($id_aplicacion>0){ ?>
                                                    <button type="button" class="btn btn-sm btn-primary" onclick="asignar();">
                                                        <i class="bi bi-people-fill"></i> Asignar
                                                    </button> &nbsp;
                                                    <button type="button" class="btn btn-sm btn-primary" onclick="roles();">
                                                        <i class="bi bi-ui-checks"></i>  Roles
                                                    </button>
                                                <?php } else { ?>
                                                    <button type="button" class="btn btn-sm btn-primary" onclick="usuario(0);">
                                                        <i class="bi bi-person-fill-add"></i> Agregar
                                                    </button>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <form id="formBusqueda" method="post" action="index.php">
                                            <div class="row">
                                                <div class="col-md-2"></div>
                                                <div class="col-md-5">
                                                    <label for="id_aplicacion" class="form-label pt-1">Aplicación</label>
                                                    <select class="form-select form-select-sm form-select-chosen shadow-sm" id="id_aplicacion" name="id_aplicacion">
                                                        <option value="0">Todas</option>
                                                        <?php for($x=1;$x<=count($catAplicaciones);$x++){ ?>
                                                            <option value="<?= $catAplicaciones[$x]["id"] ?>"><?= $catAplicaciones[$x]["nombre"] ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="status" class="form-label pt-1">Status</label>
                                                    <select class="form-select form-select-sm form-select-chosen shadow-sm" id="status" name="status">
                                                        <option value="0">Todas</option>
                                                        <?php for($x=1;$x<=count($catStatus);$x++){ ?>
                                                            <option value="<?= $catStatus[$x]["id"] ?>"><?= $catStatus[$x]["nombre"] ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-2"></div>
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
                                                        <th width="5%">Id</th>
                                                        <th width="15%">Nombre</th>
                                                        <th width="15%">A. Paterno</th>
                                                        <th width="15%">A. Materno</th>
                                                        <th width="10%">Rol</th>
                                                        <th width="10%">Area</th>
                                                        <th width="10%">Estatus</th>
                                                        <th width="15%">Accion</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php for($x=1;$x<=count($usuarios);$x++){ ?>
                                                        <tr class="text-center">
                                                            <td><?= $usuarios[$x]["id"] ?></td>
                                                            <td class="text-start pl-3"><?= $usuarios[$x]["nombre"] ?></td>
                                                            <td class="text-start"><?= $usuarios[$x]["ap_paterno"] ?></td>
                                                            <td class="text-start"><?= $usuarios[$x]["ap_materno"] ?></td>
                                                            <td><?= $usuarios[$x]["rol"] ?></td>
                                                            <td><?= $usuarios[$x]["area"] ?></td>
                                                            <td class="<?= $usuarios[$x]["status_color"] ?>"><?= $usuarios[$x]["status_icono"]." ".$usuarios[$x]["status_nombre"] ?></td>
                                                            <td>
                                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="usuario(<?= $usuarios[$x]['id'] ?>);" data-bs-toggle="tooltip" title="Información del Usuario">
                                                                    <i class="bi bi-person-lines-fill"></i>
                                                                </button>
                                                                <?php if($id_aplicacion>0){ ?>
                                                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="privilegios('<?= $usuarios[$x]['id'] ?>');" data-bs-toggle="tooltip" title="Editar Privilegios">
                                                                        <i class="bi bi-person-fill-gear"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="retirar('<?= $usuarios[$x]['id'] ?>');" data-bs-toggle="tooltip" title="Retirar de la Aplicación">
                                                                        <i class="bi bi-person-x-fill"></i>
                                                                    </button>
                                                                <?php } ?>
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

        <!-- MODAL USUARIO -->
        <div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalUsuarioCenterTitle">
                            <i class="bi bi-person-lines-fill"></i> información de usuario
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-2">
                        <iframe width="100%" frameborder="0" style="border:none;"></iframe>
                    </div>
                </div>
            </div>
        </div>

        <-- MODAL ASIGNAR USUARIOS -->
        <div class="modal fade" id="modalAsignarUsuarios" tabindex="-1" aria-labelledby="modalAsignarUsuariosTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalAsignarUsuariosCenterTitle">
                            <i class="bi bi-people-fill"></i> Asignar Usuarios a la Aplicación
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