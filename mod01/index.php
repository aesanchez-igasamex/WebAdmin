<?php
    //-- CONSTANTES --
    define("NIVEL","../");
    define("MODULO","mod01");

    //-- CONFIGURACIONES GENERALES --
    require_once(NIVEL."com/cabecera.php");

?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="shortcut icon" href="assets/images/favicon.svg">
        <title><?= TITULO ?></title>

        <!-- CSS -->
        <link rel="stylesheet" href="<?= NIVEL ?>../vendor/assetsB/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?= NIVEL ?>../vendor/assetsB/fonts/bootstrap-icons.min.css">
        <link rel="stylesheet" href="<?= NIVEL ?>../vendor/assetsB/css/main.min.css">
        <link rel="stylesheet" href="<?= NIVEL ?>../vendor/assetsB/vendor/overlay-scroll/OverlayScrollbars.min.css">
        <link rel="stylesheet" href="<?= NIVEL ?>../vendor/assetsB/vendor/datatables/dataTables.bs5.css">
        <link rel="stylesheet" href="<?= NIVEL ?>../vendor/assetsB/vendor/datatables/dataTables.bs5-custom.css">

        <!-- JAVASCRIPT -->
        <script src="<?= NIVEL ?>../vendor/assetsB/js/jquery.min.js"></script>
        <script src="<?= NIVEL ?>../vendor/assetsB/js/bootstrap.bundle.min.js"></script>
        <script src="<?= NIVEL ?>../vendor/assetsB/js/modernizr.js"></script>
        <script src="<?= NIVEL ?>../vendor/assetsB/js/moment.js"></script>
        <script src="<?= NIVEL ?>../vendor/assetsB/vendor/overlay-scroll/jquery.overlayScrollbars.min.js"></script>
        <script src="<?= NIVEL ?>../vendor/assetsB/vendor/overlay-scroll/custom-scrollbar.js"></script>
        <script src="<?= NIVEL ?>../vendor/assetsB/vendor/datatables/dataTables.min.js"></script>
        <script src="<?= NIVEL ?>../vendor/assetsB/vendor/datatables/dataTables.bootstrap.min.js"></script>
        <script type="text/javascript">
            $(document).ready(function(){
                <?php if($menuINFO["menu_tipo"]==1){ ?>
                    $("#<?php echo $menuINFO["menu_identificador"]?>").addClass("active-page-link");
                <?php } ?>

                <?php if($menuINFO["menu_tipo"]==2){ ?>
                    $("#<?php echo $menuINFO["menu_identificador"]?>").addClass("active-page-link");
                    $("#<?php echo $menuINFO["submenu_identificador"]?>").addClass("active-page-link");
                <?php } ?>
            });

            function perfil(id){
                $("#modalPerfil iframe").attr("src","<?= NIVEL ?>usuarios/editarPerfil.php?id="+id);
                $("#modalPerfil").modal("show");
            }

            function cerrarSesion(){
                $.post("<?= NIVEL ?>com/cerrarSesion.php",{ }, function(ruta){ alert("ruta: " + ruta); location.href = ruta; });
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

                        <!-- Row start -->
                        <div class="row gx-3">
                        <div class="col-xxl-8 col-sm-12 col-12">
                            <div class="card">
                            <div class="card-body">
                                <!-- Row start -->
                                <div class="row g-5">
                                <div class="col-sm-6 col-12 v-separator">
                                    <div class="card-title">Projects</div>
                                    <div class="d-flex justify-content-between">
                                    <div class="mt-auto">
                                        <h3>25%</h3>
                                        <h6 class="text-red">
                                        <i class="bi bi-arrow-down-circle-fill"></i>
                                        18.09%
                                        </h6>
                                    </div>
                                    <div id="sparkline1"></div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-12">
                                    <div class="card-title">Income</div>
                                    <div class="d-flex justify-content-between">
                                    <div class="mt-auto">
                                        <h3>67%</h3>
                                        <h6 class="text-green">
                                        <i class="bi bi-arrow-up-circle-fill"></i> 21.15%
                                        </h6>
                                    </div>
                                    <div id="sparkline2"></div>
                                    </div>
                                </div>
                                </div>
                                <!-- Row end -->
                            </div>
                            </div>
                            <!-- Row start -->
                            <div class="row gx-3">
                            <div class="col-lg-4 col-sm-6 col-12">
                                <div class="card d-flex justify-content-between flex-row p-3">
                                <div class="me-2">
                                    <p>Sales</p>
                                    <h3>3700</h3>
                                    <h6 class="text-green m-0">
                                    <i class="bi bi-arrow-up-circle-fill"></i>
                                    25.65%
                                    </h6>
                                </div>
                                <div id="sparkline3"></div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 col-12">
                                <div class="card d-flex justify-content-between flex-row p-3">
                                <div class="me-2">
                                    <p>Expenses</p>
                                    <h3>2500</h3>
                                    <h6 class="text-green m-0">
                                    <i class="bi bi-arrow-up-circle-fill"></i>
                                    16.90%
                                    </h6>
                                </div>
                                <div id="sparkline4"></div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-12 col-12">
                                <div class="card d-flex justify-content-between flex-row p-3">
                                <div class="me-2">
                                    <p>Income</p>
                                    <h3>28K</h3>
                                    <span class="badge shade-light-red">Year 2022</span>
                                </div>
                                <div id="sparkline5"></div>
                                </div>
                            </div>
                            </div>
                            <!-- Row end -->
                        </div>
                        <div class="col-xxl-4 col-sm-12 col-12">
                            <div class="card">
                            <div class="card-header">
                                <div class="card-title">Visitas</div>
                            </div>
                            <div class="card-body">
                                <div id="projects"></div>
                                <div class="text-center">
                                <span class="badge shade-light-blue me-3">+61.7% completed</span>
                                <span class="badge shade-light-red">+38.3% pending</span>
                                </div>
                            </div>
                            </div>
                        </div>
                        </div>
                        <!-- Row end -->

                        <!-- Row start -->
                        <div class="row gx-3">
                        <div class="col-xxl-8 col-xl-12 col-lg-8 col-sm-12 col-12">
                            <div class="card">
                            <div class="card-header">
                                <div class="card-title">Analytics</div>
                            </div>
                            <div class="card-body">
                                <div id="analytics"></div>
                                <div class="d-flex justify-content-center mt-3">
                                <div class="graph-stats d-flex align-items-center flex-column">
                                    <h3>9500</h3>
                                    <div class="graph-stats-details d-flex align-items-center me-2">
                                    <b class="text-red">- 21.3%</b>
                                    <p class="ms-3">
                                        <span class="text-red d-flex fw-bold mb-2">Visitors</span>Lower than last week
                                    </p>
                                    </div>
                                    <div id="sparkline-analytics1"></div>
                                </div>
                                <div class="graph-stats d-flex align-items-center flex-column">
                                    <h3>6500</h3>
                                    <div class="graph-stats-details d-flex align-items-center me-2">
                                    <b class="text-green">+ 25.5%</b>
                                    <p class="ms-3">
                                        <span class="text-yellow d-flex fw-bold mb-2">Sessions</span>Better than last week
                                    </p>
                                    </div>
                                    <div id="sparkline-analytics2"></div>
                                </div>
                                <div class="graph-stats d-flex align-items-center flex-column">
                                    <h3>3000</h3>
                                    <div class="graph-stats-details d-flex align-items-center me-2">
                                    <b class="text-green">+ 18.9%</b>
                                    <p class="ms-3">
                                        <span class="text-green d-flex fw-bold mb-2">Clicks</span>Better than last week
                                    </p>
                                    </div>
                                    <div id="sparkline-analytics3"></div>
                                </div>
                                </div>
                            </div>
                            </div>
                        </div>
                        <div class="col-xxl-4 col-xl-12 col-lg-4 col-sm-12 col-12">
                            <div class="card">
                            <div class="card-header">
                                <div class="card-title">Leads</div>
                            </div>
                            <div class="card-body">
                                <div id="leads"></div>
                                <div class="text-center my-4">
                                <h3>
                                    369
                                    <i class="bi bi-arrow-up-right-circle-fill text-green"></i>
                                </h3>
                                <p class="text-truncate">32% higher than last month</p>
                                </div>
                            </div>
                            </div>
                        </div>
                        </div>
                        <!-- Row end -->

                        <!-- Row start -->
                        <div class="row gx-3">
                        <div class="col-xxl-8 col-xl-12 col-lg-8 col-sm-12 col-12">
                            <div class="card">
                            <div class="card-body">
                                <!-- Row start -->
                                <div class="row align-items-center">
                                <div class="col-xxl-9 col-xl-8 col-md-9 col-sm-8 col-12">
                                    <div class="card-title">Visitors</div>
                                    <div id="visits"></div>
                                </div>
                                <div class="col-xxl-3 col-xl-4 col-md-3 col-sm-4 col-12">
                                    <div class="m-0">
                                    <div class="d-flex align-items-center box-bdr-red rounded-2 p-3">
                                        <i class="bi bi-tv text-red font-3x"></i>
                                        <div class="ms-3">
                                        <h3>560</h3>
                                        <p class="m-0">Desktop</p>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center box-bdr-blue rounded-2 p-3 my-2">
                                        <i class="bi bi-tablet text-blue font-3x"></i>
                                        <div class="ms-3">
                                        <h3>268</h3>
                                        <p class="m-0">iPad</p>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center box-bdr-yellow rounded-2 p-3">
                                        <i class="bi bi-phone text-yellow font-3x"></i>
                                        <div class="ms-3">
                                        <h3>957</h3>
                                        <p class="m-0">Mobile</p>
                                        </div>
                                    </div>
                                    </div>
                                </div>
                                </div>
                                <!-- Row end -->
                            </div>
                            </div>
                        </div>
                        <div class="col-xxl-4 col-xl-12 col-lg-4 col-sm-12 col-12">
                            <div class="card">
                            <div class="card-body">
                                <div class="card-title">Revenue</div>
                                <div id="revenue"></div>
                            </div>
                            </div>
                        </div>
                        </div>
                        <!-- Row end -->

                        <!-- Row start -->
                        <div class="row gx-3">
                        <div class="col-xxl-4 col-sm-12 col-12">
                            <div class="card">
                            <div class="card-body">
                                <div class="card-title">Sales</div>
                                <p>Total $57,290 Sales</p>
                                <div id="sales"></div>
                                <div class="d-flex justify-content-between ht-separator pt-4">
                                <div class="m-0">
                                    <h5>Highest Sales</h5>
                                    <p class="m-0">
                                    Total 85M Income In the month of April
                                    </p>
                                </div>
                                <a href="#" class="btn btn-info">
                                    <i class="bi bi-caret-right-fill m-0"></i>
                                </a>
                                </div>
                            </div>
                            </div>
                        </div>
                        <div class="col-xxl-4 col-sm-6 col-12">
                            <div class="card">
                            <div class="card-body">
                                <div class="card-title">Transactions</div>
                                <div id="transactions"></div>
                                <!-- Row start -->
                                <div class="row gx-3">
                                <div class="col-sm-6 col-6">
                                    <div class="d-flex mb-3">
                                    <div id="transactions1"></div>
                                    <div class="ms-2">
                                        <h3>75k</h3>
                                        <p class="m-0">Transactions</p>
                                    </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-6">
                                    <div class="d-flex mb-3">
                                    <div id="transactions2"></div>
                                    <div class="ms-2">
                                        <h3>56k</h3>
                                        <p class="m-0">Profit</p>
                                    </div>
                                    </div>
                                </div>
                                </div>
                                <!-- Row end -->
                                <div class="d-flex justify-content-between ht-separator pt-4">
                                <div class="m-0">
                                    <h5>High Income</h5>
                                    <p class="m-0">
                                    Total 36M Income In the month of April
                                    </p>
                                </div>
                                <a href="#" class="btn btn-info">
                                    <i class="bi bi-caret-right-fill m-0"></i>
                                </a>
                                </div>
                            </div>
                            </div>
                        </div>
                        <div class="col-xxl-4 col-sm-6 col-12">
                            <div class="card">
                            <div class="card-header">
                                <div class="card-title">Project Activity</div>
                            </div>
                            <div class="card-body">
                                <div class="scroll370">
                                <ul class="mt-3">
                                    <li class="activity-list d-flex">
                                    <div class="activity-time pt-2 pe-3 me-3">
                                        <p class="date m-0">10:30 am</p>
                                        <span class="badge shade-red">75%</span>
                                    </div>
                                    <div class="py-3">
                                        <h5>Bootstrap Gallery - Admin Dashboard</h5>
                                        <p>by Elnathan Lois</p>
                                        <div class="badge red">8 hours left</div>
                                    </div>
                                    </li>
                                    <li class="activity-list d-flex">
                                    <div class="activity-time pt-2 pe-3 me-3">
                                        <p class="date m-0">11:30 am</p>
                                        <span class="badge shade-blue">50%</span>
                                    </div>
                                    <div class="py-3">
                                        <h5>Mobile App</h5>
                                        <p>by Patrobus Nicole</p>
                                        <div class="stacked-images my-2">
                                        <img src="<?= NIVEL ?>../vendor/assetsB/images/user12.png" alt="Bootstrap Gallery">
                                        <img src="<?= NIVEL ?>../vendor/assetsB/images/user7.png" alt="Bootstrap Gallery">
                                        <img src="<?= NIVEL ?>../vendor/assetsB/images/user6.png" alt="Bootstrap Gallery">
                                        <img src="<?= NIVEL ?>../vendor/assetsB/images/user9.png" alt="Bootstrap Gallery">
                                        <span class="plus shade-primary">+3</span>
                                        </div>
                                    </div>
                                    </li>
                                    <li class="activity-list d-flex">
                                    <div class="activity-time pt-2 pe-3 me-3">
                                        <p class="date m-0">12:50 pm</p>
                                        <span class="badge shade-yellow">90%</span>
                                    </div>
                                    <div class="py-3">
                                        <h5>Material Design Kit</h5>
                                        <p>by Abilene Omega</p>
                                        <div class="badge blue mb-2">2 days left</div>
                                        <div class="stacked-images my-2">
                                        <img src="<?= NIVEL ?>../vendor/assetsB/images/user11.png" alt="Bootstrap Gallery">
                                        <img src="<?= NIVEL ?>../vendor/assetsB/images/user10.png" alt="Bootstrap Gallery">
                                        <img src="<?= NIVEL ?>../vendor/assetsB/images/user13.png" alt="Bootstrap Gallery">
                                        <span class="plus shade-green">+2</span>
                                        </div>
                                    </div>
                                    </li>
                                    <li class="activity-list d-flex">
                                    <div class="activity-time pt-2 pe-3 me-3">
                                        <p class="date m-0">02:30 pm</p>
                                        <span class="badge shade-green">50%</span>
                                    </div>
                                    <div class="py-3">
                                        <h5>Invoice Design</h5>
                                        <p>by Shelomi Sarah</p>
                                        <div class="badge green">2 days left</div>
                                    </div>
                                    </li>
                                    <li class="activity-list d-flex">
                                    <div class="activity-time pt-2 pe-3 me-3">
                                        <p class="date m-0">03:45 pm</p>
                                        <span class="badge shade-red">77%</span>
                                    </div>
                                    <div class="py-3">
                                        <h5>Mobile App</h5>
                                        <p>by Anaiah Edrei</p>
                                        <div class="badge yellow">3 days left</div>
                                    </div>
                                    </li>
                                    <li class="activity-list d-flex">
                                    <div class="activity-time pt-2 pe-3 me-3">
                                        <p class="date m-0">04:10 pm</p>
                                        <span class="badge shade-blue">89%</span>
                                    </div>
                                    <div class="py-3">
                                        <h5>Backend Development</h5>
                                        <p>by Mareshah Nicole</p>
                                        <div class="stacked-images mt-3">
                                        <img src="<?= NIVEL ?>../vendor/assetsB/images/user4.png" alt="Bootstrap Gallery">
                                        <img src="<?= NIVEL ?>../vendor/assetsB/images/user10.png" alt="Bootstrap Gallery">
                                        <img src="<?= NIVEL ?>../vendor/assetsB/images/user17.png" alt="Bootstrap Gallery">
                                        <span class="plus shade-yellow">+7</span>
                                        </div>
                                    </div>
                                    </li>
                                    <li class="activity-list d-flex">
                                    <div class="activity-time pt-2 pe-3 me-3">
                                        <p class="date m-0">04:45 pm</p>
                                        <span class="badge shade-yellow">25%</span>
                                    </div>
                                    <div class="py-3">
                                        <h5>Testing</h5>
                                        <p>by Andrew Seth</p>
                                        <div class="badge red">4 days left</div>
                                    </div>
                                    </li>
                                    <li class="activity-list d-flex">
                                    <div class="activity-time pt-2 pe-3 me-3">
                                        <p class="date m-0">05:30 pm</p>
                                        <span class="badge shade-green">85%</span>
                                    </div>
                                    <div class="py-3">
                                        <h5>Product Launch</h5>
                                        <p>by Berechiah Philip</p>
                                        <div class="badge blue">3 days left</div>
                                    </div>
                                    </li>
                                </ul>
                                </div>
                            </div>
                            </div>
                        </div>
                        </div>
                        <!-- Row end -->

                    </div>
                    <!-- INFERIOR FIN -->

                </div>
                <!-- CONTENIDO FIN -->

                <div class="app-footer">
                    <span><?= COPYRIGHT?></span>
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
                            <i class="bi bi-person-square"></i> Editar Usuario
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-2">
                        <iframe width="100%" frameborder="0" style="border:none;"></iframe>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL PERMISOS -->
        <div class="modal fade" id="modalPermisos" tabindex="-1" aria-labelledby="modalRegistroTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalRegistroCenterTitle">
                            <i class="bi bi-person-square"></i> Editar Usuario
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

        <!-- Apex Charts -->
        <script src="<?= NIVEL ?>../vendor/assetsB/vendor/apex/apexcharts.min.js"></script>
        <script src="<?= NIVEL ?>../vendor/assetsB/vendor/apex/custom/dash4/sparkline.js"></script>
        <script src="<?= NIVEL ?>../vendor/assetsB/vendor/apex/custom/dash4/projects.js"></script>
        <script src="<?= NIVEL ?>../vendor/assetsB/vendor/apex/custom/dash4/visits.js"></script>
        <script src="<?= NIVEL ?>../vendor/assetsB/vendor/apex/custom/dash4/analytics.js"></script>
        <script src="<?= NIVEL ?>../vendor/assetsB/vendor/apex/custom/dash4/revenue.js"></script>
        <script src="<?= NIVEL ?>../vendor/assetsB/vendor/apex/custom/dash4/leads.js"></script>
        <script src="<?= NIVEL ?>../vendor/assetsB/vendor/apex/custom/dash4/sales.js"></script>
        <script src="<?= NIVEL ?>../vendor/assetsB/vendor/apex/custom/dash4/transactions.js"></script>
        <script src="<?= NIVEL ?>../vendor/assetsB/vendor/apex/custom/dash4/transactions2.js"></script>
        <script src="<?= NIVEL ?>../vendor/assetsB/vendor/apex/custom/dash4/analytics-sparkline.js"></script>

        <script src="<?= NIVEL ?>../vendor/assetsB/vendor/newsticker/newsTicker.min.js"></script>
        <script src="<?= NIVEL ?>../vendor/assetsB/vendor/newsticker/custom-newsTicker.js"></script>
        <script src="<?= NIVEL ?>../vendor/assetsB/js/main.js"></script>
    </body>

</html>