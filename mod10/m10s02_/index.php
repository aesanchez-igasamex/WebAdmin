    <?php
    //-- CONSTANTES --
    define("NIVEL","../../");
    define("MODULO","m10s02");

    //-- CONFIGURACIONES GENERALES --
    require_once(NIVEL."com/cabecera.php");

    //-- PARAMETROS Y VARIABLES --
    $id_sistema = ($_POST["id_sistema"]>0)? $_POST["id_sistema"] : 0 ;
    $id_actividad_regulada = ($_POST["id_actividad_regulada"]>0)? $_POST["id_actividad_regulada"] : 0 ;
    $id_status = (isset($_POST["id_status"]))? $_POST["id_status"] : 1 ;
    $reg = array();
    $cont = 1;

    //-- CONECTA A LA BASE DE DATOS --
    include(NIVEL."../com/configBD_Admin.php");
    include(NIVEL."../com/configBD_SAG.php");
    include(NIVEL."../com/configBD_SCADA.php");
    include(NIVEL."../com/configBD_CV.php");

    //-- OBTIENE DATOS DE LOS SISTEMAS --
    $sql = "SELECT a.id, a.tipo_erm, a.nombre, b.nombre, a.preciso, a.id_erm, a.permiso, a.fecha_op_ini, a.fecha_op_fin,
                    c.nombre, c.icono, c.color
            FROM clientes_emr AS a
            JOIN cat_sociedad_mercantil AS b ON a.sociedad_mercantil = b.id
            JOIN cat_status_emr AS c ON a.status = c.id
            WHERE a.tipo_erm = ?
            AND a.status = ?";
    $params[] = 'Interconexion';
    $params[] = $id_status;
    if($id_sistema>0){ $sql .= " AND a.id = ?"; $params[] = $id_sistema; }
    if($id_actividad_regulada>0){ $sql .= " AND a.actividad_regulada = ?"; $params[] = $id_actividad_regulada; }
    $sql .= " ORDER BY a.nombre ASC";

    $prepare = $dbSAG->prepare($sql);
    $result = $dbSAG->execute($prepare,$params);
    $params = array();
    while(!$result->EOF){
        $reg[$cont]["id"] = $result->fields[0];
        $reg[$cont]["tipo"] = $result->fields[1];
        $reg[$cont]["negrita"] = "font-weight-bold";
        $reg[$cont]["clase"] = $result->fields[0]."x";
        $reg[$cont]["nombre"] = $result->fields[2]." ".$result->fields[3];
        $reg[$cont]["nombre"] .= (strlen($result->fields[4])>0)? " (".$result->fields[4].")" : "" ;
        $reg[$cont]["id_emr"] = $result->fields[5];
        $reg[$cont]["permiso"] = $result->fields[6];
        $reg[$cont]["operacion_ini"] = $result->fields[7];
        $reg[$cont]["operacion_fin"] = $result->fields[8];
        $reg[$cont]["status_nombre"] = $result->fields[9];
        $reg[$cont]["status_icono"] = $result->fields[10];
        $reg[$cont]["status_color"] = $result->fields[11];

        /*
        //-- INFORMACION DE MEDICION --
        $val = infoSCADA($result->fields[5]);
        $reg[$cont]["medicion_nombre"]= $val["nombre"];

        //-- INFORMACION DE CV --
        $val = infoCV($result->fields[5]);
        $reg[$cont]["cv_nombre"] = $val["nombre"];
        */

        $cont++;

        $params[] = $result->fields[5];
        $sql = "SELECT a.id, a.tipo_erm, a.nombre, b.nombre, a.preciso, a.id_erm, a.permiso,
                        c.nombre, c.icono, c.color
                FROM clientes_emr AS a
                JOIN cat_sociedad_mercantil AS b ON a.sociedad_mercantil = b.id
                JOIN cat_status_emr AS c ON a.status = c.id
                WHERE a.tipo_erm <> 'Interconexion'
                AND a.id_inter = ?
                ORDER BY a.nombre ASC";
        $prepare = $dbSAG->prepare($sql);
        $result2 = $dbSAG->execute($prepare,$params);
        $params = array();
        while(!$result2->EOF){
            $reg[$cont]["id"] = $result2->fields[0];
            $reg[$cont]["tipo"] = $result2->fields[1];
            $reg[$cont]["negrita"] = "";
            $reg[$cont]["clase"] = $result->fields[0]."x";
            $reg[$cont]["nombre"] = $result2->fields[2]." ".$result2->fields[3];
            $reg[$cont]["nombre"] .= (strlen($result2->fields[4])>0)? " (".$result2->fields[4].")" : "" ;
            $reg[$cont]["id_emr"] = $result2->fields[5];
            $reg[$cont]["permiso"] = "-";

            $reg[$cont]["status_nombre"] = $result2->fields[7];
            $reg[$cont]["status_icono"] = $result2->fields[8];
            $reg[$cont]["status_color"] = $result2->fields[9];

            $result2->MoveNext();
            $cont++;
        }

        $result->MoveNext();
    }

    //-- CIERRA CONEXION A LA BASE DE DATOS --
    if($result) $result->close();
    $dbAdmin->close();
    $dbSAG->close();


    /*
    * Obtiene la informacion de la Estacion de Medicion desde la BD de SCADA
    * @param $id_emr : id de la estacion de medicion
    * @return array : array con informacion de la estacion de medicion
    */
    function infoSCADA($id_emr) {
        global $dbSCADA;
        $params = array($id_emr);
        $val = array();
        $val["encontrado"] = false;

        $prepare = $dbSCADA->prepare("SELECT * FROM clientes_emr WHERE id_erm = ?");
        $result = $dbSCADA->execute($prepare,$params);
        if (!$result->EOF) {
            $val["encontrado"] = true;
            $val["nombre"] = $result->fields["razon_social"];
        }

        return $val;
    }

    /*
    * Obtiene la informacion de la Estacion de Medicion desde la BD de Controles Volumetricos
    * @param $id_emr : id de la estacion de medicion
    * @return array : array con informacion de la estacion de medicion
    */
    function infoCV($id_emr) {
        global $dbCV;
        $params = array($id_emr);
        $val = array();
        $val["encontrado"] = false;

        $sql = "SELECT permisionario
                FROM tbl_estaciones
                WHERE id_erm = ?";
        $prepare = $dbCV->prepare($sql);
        $result = $dbCV->execute($prepare,$params);
        if (!$result->EOF) {
            $val["encontrado"] = true;
            $val["nombre"] = mb_convert_encoding($result->fields["permisionario"], 'UTF-8', 'ISO-8859-1');
        }

        return $val;
    }
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
                    $("#<?php echo $menuINFO["menu_identificador"]?>").addClass("active-page-link");
                <?php } ?>

                <?php if($menuINFO["menu_tipo"]==2){ ?>
                    $("#<?php echo $menuINFO["menu_identificador"]?>").addClass("active-page-link");
                    $("#<?php echo $menuINFO["submenu_identificador"]?>").addClass("active-page-link");
                <?php } ?>


                $("#id_status option:selected").removeAttr("selected");
                $("#id_status option[value='<?= $status; ?>']").attr('selected', 'selected');
                $("#id_status").css("background-color", "#cdffc9");

                $("#id_status").change(function(){ $("#formBusqueda").submit(); });
            });

            function editarInterconexion(id){
                $("#modalInterconexion iframe").attr("src","editarInterconexion.php?id="+id);
                $("#modalInterconexion").modal("show");
            }

            function tamano(alto, modal){
                $("#"+modal+" iframe").removeAttr("height");
                $("#"+modal+" iframe").attr("height",alto);
            }

            function recargar(){
                $("#modalInterconexion").modal("hide");
                location.reload();
            }

            function coordenadas(id){
                $("#modalCoordenadas iframe").attr("src","verMapa.php?id_sistema="+id);
                $("#modalCoordenadas").modal("show");
            }


            function salir(){
                $("#modalSalir").modal("show");
            }

            function perfil(id){
                $("#modalPerfil iframe").attr("src","<?= NIVEL ?>usuarios/editarPerfil.php?id="+id);
                $("#modalPerfil").modal("show");
            }

            function cerrarSesion(){
                $.post("<?= NIVEL ?>com/cerrarSesion.php",{ }, function(ruta){ location.href = ruta; });
            }

            function expande(clave) {
                $("." + clave).each(function() {
                    if ($(this).hasClass("show")) {
                        $(this).removeClass("show");
                        $("#" + clave).html("<i class='bi bi-plus-lg'></i>");
                    } else {
                        $(this).addClass("show");
                        $("#" + clave).html("<i class='bi bi-dash-lg'></i>");
                    }
                });
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
                                        <div class="col-2  d-flex align-items-end flex-column ">
                                            <button type="button" class="btn btn-primary" onclick="registro(0);">
                                                <i class="bi bi-pencil-square"></i> Nuevo
                                            </button>
                                        </div>

                                    </div>
                                    <form id="formBusqueda" method="post" action="index.php">
                                        <div class="card-body row">

                                            <div class="col-md-1"><label for="id_status" class="form-label">Status</label></div>
                                            <div class="col-md-3">
                                                <select class="form-select" id="id_status" name="id_status">
                                                    <option selected disabled value="">Seleccione</option>
                                                    <option value="1">Activo</option>
                                                    <option value="0">Inactivo</option>
                                                </select>
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

                                            <table class="table custom-table table-hover" width="100%" cellspacing="0">
                                                <thead>
                                                    <tr class="small text-center">
                                                        <th width="3%"></th>
                                                        <th width="25%">Nombre</th>
                                                        <th width="7%">Id</th>
                                                        <th class="oculta" width="10%">Permiso</th>
                                                        <th class="oculta" width="10%">Operacion Inicio/Fin</th>
                                                        <th class="oculta" width="10%">Anual</th>
                                                        <th class="oculta" width="10%">Zona</th>
                                                        <th class="oculta" width="5%">Estatus</th>
                                                        <th width="5%">Acción</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php for($x=1;$x<=count($reg);$x++){ ?>
                                                        <tr class="small text-center <?= $reg[$x]["negrita"] ?> <?= ($reg[$x]["tipo"] != "Interconexion") ? $reg[$x]["clase"] . " collapse" : "" ; ?> colapsado">
                                                            <td>
                                                                <?php if ($reg[$x]["tipo"] == "Interconexion") { ?>
                                                                    <div class="btn btn-sm btn-light text-primary btnxs colapsado boss" id="<?= $reg[$x]["clase"] ?>" onclick="expande('<?= $reg[$x]['clase'] ?>');">
                                                                        <i class="bi bi-plus-lg"></i>
                                                                    </div>
                                                                <?php } ?>
                                                            </td>
                                                            <td class="text-start">
                                                                <?= $reg[$x]["nombre"] ?>
                                                                <div class="box-bdr-blue text-blue rounded"><?= "- ".$reg[$x]["medicion_nombre"] ?></div>
                                                                <div class="box-bdr-green text-success rounded"><?= "- ".$reg[$x]["cv_nombre"] ?></div>
                                                            </td>
                                                            <td><?= $reg[$x]["id_emr"] ?></td>
                                                            <td><?= $reg[$x]["permiso"] ?></td>
                                                            <td><?= $reg[$x]["operacion_ini"] ?></td>
                                                            <td><?= $reg[$x]["x"] ?></td>
                                                            <td><?= $reg[$x]["x"] ?></td>
                                                            <td class="<?= $reg[$x]["status_color"] ?>"><?= $reg[$x]["status_icono"]." ".$reg[$x]["status_nombre"] ?></td>
                                                            <td>
                                                                <button type="button" class="btn btn-sm btn-primary" onclick="<?= ($reg[$x]["tipo"]=="Interconexion")? "editarInterconexion(".$reg[$x]["id"].")" : "editarCliente(".$reg[$x]["id"].")" ; ?>">
                                                                    <i class="bi bi-pencil-square"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-sm btn-primary" onclick="coordenadas(<?= $reg[$x]['id'] ?>);">
                                                                    <i class="bi bi-geo-fill"></i>
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

        <!-- MODAL INTERCONEXION -->
        <div class="modal fade" id="modalInterconexion" tabindex="-1" aria-labelledby="modalInterconexionTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalInterconexionCenterTitle">
                            <i class="bi bi-pencil-square"></i> Editar Sistema
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-2">
                        <iframe width="100%" frameborder="0" style="border:none;"></iframe>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL COORDENADAS -->
        <div class="modal fade" id="modalCoordenadas" tabindex="-1" aria-labelledby="modalCoordenadasTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalCoordenadasCenterTitle">
                            <i class="bi bi-pencil-square"></i> Editar Coordenadas
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