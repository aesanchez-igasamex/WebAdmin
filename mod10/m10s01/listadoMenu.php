<?php
    //-- CONSTANTES --
    define("NIVEL","../../");
    define("APLICACION","100");

    //-- PARAMETROS Y VARIABLES --
    $id_aplicacion = $_GET["id_aplicacion"];
    $menu = array();
    $cont = 1;

    //-- CONECTA A LA BASE DE DATOS --
    include(NIVEL."../com/config.php");
    $dbAdmin = conectaBD("mysql","admin");

    //-- OBTIENE NOMBRE DE LA APLICACION --
    $prepare = $dbAdmin->prepare("SELECT * FROM cat_aplicaciones WHERE id = ?");
    $result = $dbAdmin->execute($prepare,[$id_aplicacion]);
    $nombre_aplicacion = (!$result->EOF) ? $result->fields["nombre"] . " - " .  $result->fields["descripcion"] : " Nombre de aplicación no encontrado";

    //-- OBTIENE LISTADO DE MENUS Y SUBMENUS --
    $prepare = $dbAdmin->prepare("SELECT * FROM tbl_menu WHERE sistema = ? ORDER BY orden ASC");
    $result = $dbAdmin->execute($prepare,[$id_aplicacion]);
    while(!$result->EOF){
        $menu[$cont]["id"] = $result->fields["id"];
        $menu[$cont]["nombre"] = $result->fields["nombre"];
        $menu[$cont]["identificador"] = $result->fields["identificador"];
        $menu[$cont]["directorio"] = $result->fields["directorio"];
        $menu[$cont]["icono"] = $result->fields["icono"];
        $menu[$cont]["orden"] = $result->fields["orden"];
        $menu[$cont]["padre"] = ($result->fields["padre"]>0) ? $result->fields["padre"] : "-";
        $menu[$cont]["status"] = $result->fields["status"];
        $menu[$cont]["status_nombre"] = ($result->fields["status"]==1)? "Activo" : "Inactivo";
        $menu[$cont]["status_color"] = ($result->fields["status"]==1)? "success" : "danger";
        $menu[$cont]["status_icono"] = ($result->fields["status"]==1)? "<i class='bi bi-check-circle-fill'></i>" : "<i class='bi bi-x-circle-fill'></i>";
        $menu[$cont]["tipo"] = ($result->fields["tipo"]==1) ? "Menú" : "Submenú";
        $menu[$cont]["estilo"] = ($result->fields["tipo"]==1) ? "table-secondary font-weight-bold" : "table-light";
        $menu[$cont]["tab"] = ($result->fields["tipo"]==1) ? "" : "&nbsp;&nbsp;&nbsp;&nbsp;";
        $result->MoveNext();
        $cont++;
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
            $(document).ready(function(){
                window.parent.tamano(650,"modalMenu");
            });

            function menu(id) {
                $("#modalMenu iframe").attr("src", "editarMenu.php?id_aplicacion=<?= $id_aplicacion ?>&id=" + id);
                $("#modalMenu").modal("show");
            }

            function submenu(id) {
                $("#modalSubmenu iframe").attr("src", "editarSubmenu.php?id_aplicacion=<?= $id_aplicacion ?>&id=" + id);
                $("#modalSubmenu").modal("show");
            }

            function tamano(alto, modal){
                $("#"+modal+" iframe").removeAttr("height");
                $("#"+modal+" iframe").attr("height",alto);
            }

            function cerrar(modal){
                $("#"+modal+" iframe").removeAttr("src");
                $("#"+modal).modal("hide");
            }

            function recargar(modal){
                $("#"+modal).modal("hide");
                location.reload();
            }

        </script>

    </head>

    <body>
        <div class="page-wrapper">



            <div class="content">
                <div class="card">
                    <div class="card-header">

                        <div class="row">
                            <div class="col-8">
                                <h3 class="card-title"><?= $nombre_aplicacion ?></h3>
                            </div>
                            <div class="col-4">
                                <div class="text-end">
                                    <button type="button" class="btn btn-sm btn-primary" onclick="menu(0)"> <i class="bi bi-plus-lg"></i> Menu </button>
                                    <button type="button" class="btn btn-sm btn-primary" onclick="submenu(0)"> <i class="bi bi-plus-lg"></i> Submenu </button>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row gx-3">
                            <div class="col-xxl-12">
                                <div class="table-responsive">
                                    <table class="table m-0">
                                        <thead>
                                            <tr class="text-center">
                                                <th>ID</th>
                                                <th>Nombre</th>
                                                <th>Identificador</th>
                                                <th>Directorio</th>
                                                <th>Orden</th>
                                                <th>Tipo</th>
                                                <th>Padre</th>
                                                <th>Status</th>
                                                <th>Accion</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php for($x=1; $x<=count($menu); $x++){ ?>
                                                <tr class="text-center <?= $menu[$x]["estilo"] ?>">
                                                    <td><?= $menu[$x]["id"] ?></td>
                                                    <td class="text-start"><?= $menu[$x]["tab"] . $menu[$x]["icono"] . " " . $menu[$x]["nombre"] ?></td>
                                                    <td><?= $menu[$x]["identificador"] ?></td>
                                                    <td><?= $menu[$x]["directorio"] ?></td>
                                                    <td><?= $menu[$x]["orden"] ?></td>
                                                    <td><?= $menu[$x]["tipo"] ?></td>
                                                    <td><?= $menu[$x]["padre"] ?></td>
                                                    <td class="text-<?= $menu[$x]["status_color"] ?>"><?= $menu[$x]["status_icono"] ?> <?= $menu[$x]["status_nombre"] ?></td>
                                                    <td>
                                                        <?php if($menu[$x]["tipo"] == "Menú"){ ?>
                                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="menu(<?= $menu[$x]['id'] ?>)">
                                                                <i class="bi bi-pencil-square"></i>
                                                            </button>
                                                        <?php } else { ?>
                                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="submenu(<?= $menu[$x]['id'] ?>)">
                                                                <i class="bi bi-pencil-square"></i>
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

        </div>


        <!-- MODAL MENU -->
        <div class="modal fade" id="modalMenu" tabindex="-1" aria-labelledby="modalMenuTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalMenuCenterTitle">
                            <i class="bi bi-window"></i> Configurar Menú
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-2">
                        <iframe width="100%" frameborder="0" style="border:none;"></iframe>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL SUBMENU -->
        <div class="modal fade" id="modalSubmenu" tabindex="-1" aria-labelledby="modalSubmenuTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalSubmenuCenterTitle">
                            <i class="bi bi-window"></i> Configurar Submenú
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-2">
                        <iframe width="100%" frameborder="0" style="border:none;"></iframe>
                    </div>
                </div>
            </div>
        </div>

    </body>
</html>