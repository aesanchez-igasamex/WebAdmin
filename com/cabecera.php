<?php
    //-- CONSTANTES --
    $protocol = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS']=='on' || $_SERVER['HTTPS']==1))? 'https://' : 'http://' ;
    define("RUTA", $protocol.$_SERVER['HTTP_HOST']."/WebAdmin");
    define("TITULO", "IGASAMEX");
    define("COPYRIGHT", "IGASAMEX");
    define("APLICACION",100);

    //-- DEFINE ZONA HORARIA --
    date_default_timezone_set("America/Mexico_City");

    //-- VARIABLES --
    $info_ruta = pathinfo($_SERVER['SCRIPT_NAME']);
    $directorios = explode('/',$info_ruta['dirname']);
    $directorio = $directorios[count($directorios)-1];

    //-- OBTIENE INFORMACION DE LA SESION --
    session_start();

    //-- CONFIGURACION --
    require_once(NIVEL."../com/config.php");
    $dbAdmin = conectaBD("mysql","admin");

    //-- ACTUALIZA SESION DESDE BASE DE DATOS --
    actualizaUsuarioSesion($_SESSION["usuario_id"]);

    //-- VALIDA PRIVILEGIOS --
    $sql = "SELECT b.directorio, a.id_usuario
            FROM tbl_usuarios_menu AS a
            JOIN tbl_menu AS b ON a.id_menu = b.id
            WHERE a.id_usuario = ?
            AND b.identificador = ?
            AND b.sistema = ?";
    $prepare = $dbAdmin->prepare($sql);
    $result = $dbAdmin->execute($prepare, [$_SESSION["usuario_id"], $directorio, APLICACION]);

    //-- SI NO TIENE PRIVILEGIOS --
    if($result->EOF) header("Location: ".NIVEL."com/privilegios.php");
    if($result) $result->close();

    //-- DIBUJA SECCIONES --
    $menuINFO = menuInformacion(MODULO);
    $menuSuperior = menuSuperior();
    $menuInferior = menuInferior();
    $generalPerfil = generalPerfil();
    $generalMensajes = generalMensajes($_SESSION["usuario_id"]);
    $generalNotificaciones = generalNotificaciones($_SESSION["usuario_id"]);
    $generalModales = modales();

    //-- CIERRA CONEXION A LA BASE DE DATOS --
    if($result) $result->close();
    $dbAdmin->close();


    /******************************************************/
    /**  ACTUALIZA INFORMACION DEL USUARIO EN LA SESION  **/
    /******************************************************/
    function actualizaUsuarioSesion($id_usuario){
        global $dbAdmin;

        //-- INFORMACION DEL USUARIO --
        $prepare = $dbAdmin->prepare("SELECT * FROM tbl_usuarios WHERE id = ?");
        $resUsr = $dbAdmin->execute($prepare, [$id_usuario]);
        $_SESSION["usuario_nombre"] = $resUsr->fields["nombre"]." ".$resUsr->fields["ap_paterno"]." ".$resUsr->fields["ap_materno"];
        $_SESSION["usuario_foto"] = (strlen($resUsr->fields["foto"])>0)? $resUsr->fields["foto"] : "default.png" ;

        //-- ROL ASIGNADO AL USUARIO --
        $sql = "SELECT a.id_rol, b.nombre
                FROM tbl_usuarios_rol AS a
                JOIN cat_roles AS b ON a.id_rol = b.id
                WHERE a.id_usuario = ?
                AND b.aplicacion = ?";
        $prepare = $dbAdmin->prepare($sql);
        $resRol = $dbAdmin->execute($prepare,[$id_usuario,APLICACION]);
        $_SESSION["rol_id"] = $resRol->fields[0];
        $_SESSION["rol_nombre"] = $resRol->fields[1];
        if($resRol) $resRol->close();


        //-- BUSCA EL AREA ASIGNADA AL USUARIO --
        $sql = "SELECT a.id_area, b.nombre
                FROM tbl_usuarios_area AS a
                JOIN cat_areas AS b ON a.id_area = b.id
                WHERE a.id_usuario = ?
                AND b.aplicacion = ?";
        $prepare = $dbAdmin->prepare($sql);
        $resArea = $dbAdmin->execute($prepare,[$id_usuario,APLICACION]);
        $_SESSION["area_id"] = $resArea->fields[0];
        $_SESSION["area_nombre"] = $resArea->fields[1];
        if($resArea) $resArea->close();

        if($resUsr) $resUsr->close();
    }


    /*************************************/
    /**  OBTIENE INFORMACION DEL MODULO **/
    /*************************************/
    function menuInformacion($modulo){
        global $dbAdmin;

        //-- OBTIENE MENU --
        $sql = "SELECT id, nombre, icono, tipo, padre
                FROM tbl_menu
                WHERE identificador = ?
                AND sistema = ?
                AND status = ?";
        $prepare = $dbAdmin->prepare($sql);
        $result = $dbAdmin->execute($prepare, [$modulo, APLICACION, 1]);
        $menuINFO["menu_id"] = $result->fields[0];
        $menuINFO["menu_nombre"] = $result->fields[1];
        $menuINFO["menu_icono"] = $result->fields[2];
        $menuINFO["menu_tipo"] = $result->fields[3];
        $menuINFO["menu_padre"] = $result->fields[4];
        $menuINFO["menu_identificador"] = $modulo;
        if($result) $result->close();

        //-- SI ES SUBMENU OBTIENE EL MENU PRINCIPAL --
        if($menuINFO["menu_tipo"]==2){
            $sql = "SELECT identificador
                    FROM tbl_menu
                    WHERE id = ?
                    AND sistema = ?
                    AND status = ?";
            $prepare = $dbAdmin->prepare($sql);
            $result = $dbAdmin->execute($prepare, [$menuINFO["menu_padre"], APLICACION, 1]);
            $menuINFO["menu_identificador"] = $result->fields[0];
            $menuINFO["submenu_identificador"] = $modulo;
            if($result) $result->close();
        }

        return $menuINFO;
    }


    /************************************/
    /**  DIBUJA HTML DEL MENU SUPERIOR **/
    /************************************/
    function menuSuperior(){
        $menuHTML = "<div class=\"sidebar-custom-nav\">".
                    "  <a href=\"#\" onclick=\"mensajero(".$_SESSION["usuario_id"].");\"> <i class=\"bi bi-envelope\"></i> <span>Mensajes</span> </a>".
                    "  <a href=\"#\" onclick=\"perfil(".$_SESSION["usuario_id"].");\"> <i class=\"bi bi-person-bounding-box\"></i> <span>Mi perfil</span> </a>".
                    "  <a href=\"#\" onclick=\"soporte(".$_SESSION["usuario_id"].");\"> <i class=\"bi bi-headset\"></i> <span>Soporte</span> </a>".
                    "  <a href=\"#\" onclick=\"configuracion(".$_SESSION["usuario_id"].");\">  <i class=\"bi bi-gear\"></i> <span>Configuracion</span> </a>".
                    "</div>";
        return $menuHTML;
    }


    /************************************/
    /**  DIBUJA HTML DEL MENU INFERIOR **/
    /************************************/
    function menuInferior(){
        global $dbAdmin;
        $id_usuario = $_SESSION["usuario_id"];

        //-- OBTIENE MENU --
        $sql = "SELECT a.id_menu, b.nombre, b.identificador, b.directorio, b.icono
                FROM tbl_usuarios_menu AS a
                JOIN tbl_menu AS b ON a.id_menu = b.id
                WHERE a.id_usuario = '$id_usuario'
                AND b.tipo = 1
                AND b.status = 1
                AND b.sistema = ".APLICACION."
                ORDER by b.orden ASC";
        $result = $dbAdmin->execute($sql);

        $menuHTML = "<div class=\"sidebar-menu\">".
                    " <div class=\"sidebarMenuScroll\">".
                    "  <ul>";

        while(!$result->EOF){
            $menu_id = $result->fields[0];
            $menu_nombre = mb_convert_encoding($result->fields[1],"UTF-8");
            $menu_identificador = mb_convert_encoding($result->fields[2],"UTF-8");
            $menu_directorio = mb_convert_encoding($result->fields[3],"UTF-8");
            $menu_icono = $result->fields[4];

            //-- BUSCA SI TIENE SUBMENU --
            $sql = "SELECT b.nombre, b.identificador, b.directorio, b.icono
                    FROM tbl_usuarios_menu AS a
                    JOIN tbl_menu AS b ON a.id_menu = b.id
                    WHERE a.id_usuario = '$id_usuario'
                    AND b.padre = $menu_id
                    AND b.tipo = 2
                    AND b.status = 1
                    AND b.sistema = ".APLICACION."
                    ORDER by b.orden ASC";
            $result2 = $dbAdmin->execute($sql);

            //-- SI TIENE SUBMENU --
            if($result2->recordCount() > 0){
                $menuHTML .= "  <li class=\"sidebar-dropdown\" id=\"$menu_identificador\">".
                             "   <a  href=\"#\">".
                             "    $menu_icono <span class=\"menu-text\">$menu_nombre</span>".
                             "   </a>".
                             "   <div class=\"sidebar-submenu\">".
                             "    <ul>";


                while(!$result2->EOF){
                    $submenu_nombre = mb_convert_encoding($result2->fields[0],"UTF-8");
                    $submenu_identificador = $result2->fields[1];
                    $submenu_directorio = $result2->fields[2];
                    $submenu_icono = $result2->fields[3];
                    $menuHTML .= "<li><a id=\"$submenu_identificador\" href=\"".RUTA."/$submenu_directorio\" onClick=\"$(window.parent.document.body).loadingModal({ text: 'Cargando datos, espere...', animation: 'cubeGrid' });\">$submenu_icono <span>$submenu_nombre</span></a></li>";
                    $result2->MoveNext();
                }
                $menuHTML .= "  </ul>".
                             " </div>".
                             "</li>";
            }
            //-- SI NO TIENE SUBMENU --
            else{
                $menuHTML .= "  <li id=\"$menu_identificador\">".
                             "   <a href=\"".RUTA."/$menu_directorio\" onClick=\"$(window.parent.document.body).loadingModal({ text: 'Cargando datos, espere...', animation: 'cubeGrid' });\">".
                             "    $menu_icono <span class=\"menu-text\">$menu_nombre</span>".
                             "   </a>".
                             "  </li>";

            }

            $result->MoveNext();
        }
        $menuHTML .= "  <li id=\"logout\">".
                     "   <a href=\"#\" onclick=\"salir();\">".
                     "    <i class=\"bi bi-door-open-fill\"></i></i> <span>Cerrar Sesion</span></a>".
                     "   </a>".
                     "  </li>";

        return $menuHTML;
    }


    //-------------------------------------------
    //-- FUNCIONALIDAD DE LA SECCION MI PERFIL --
    //-------------------------------------------
    function generalPerfil(){
        $perfil= "<div class=\"header-profile d-flex align-items-center\">
                        <div class=\"dropdown\">
                            <a href=\"#\" id=\"userSettings\" class=\"user-settings\" data-toggle=\"dropdown\" aria-haspopup=\"true\">
                                <span class=\"user-name d-none d-md-block\">".$_SESSION["usuario_nombre"]."</span>
                                <span class=\"avatar\">
                                    <img src=\"".NIVEL."mod10/m10s03/imagenes/".$_SESSION["usuario_foto"]."?".rand(1,1000)."\" alt=\"Admin Templates\"> <span class=\"status online\"></span>
                                </span>
                            </a>
                            <div class=\"dropdown-menu dropdown-menu-end\" aria-labelledby=\"userSettings\">
                                <div class=\"header-profile-actions\">
                                    <a href=\"#\" onclick=\"perfil(".$_SESSION["usuario_id"].");\">Mi Perfil</a>
                                    <a href=\"#\" onclick=\"salir();\">Cerrar Sesión</a>
                                </div>
                            </div>
                        </div>
                    </div>";
        return $perfil;
    }

    //---------------------------------------------
    //-- FUNCIONALIDAD DE LA SECCION DE MENSAJES --
    //---------------------------------------------
    function generalMensajes($id_usuario){
        $mensajes = "<div class=\"header-actions d-xl-flex d-lg-none gap-4\">
                        <div class=\"dropdown\">
                            <a class=\"dropdown-toggle\" href=\"#!\" role=\"button\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\">
                                <i class=\"bi bi-envelope-open fs-5 lh-1\"></i> <span class=\"count-label\">9</span>
                            </a>
                            <div class=\"dropdown-menu dropdown-menu-end shadow-lg\">
                                <div class=\"dropdown-item\">
                                    <div class=\"d-flex py-2 border-bottom\">
                                        <img src=\"".NIVEL."../vendor/assetsB/images/user.png\" class=\"img-3x me-3 rounded-3\" alt=\"Admin Dashboards\">
                                        <div class=\"m-0\">
                                            <h6 class=\"mb-1 fw-semibold\">Sophie Michiels</h6>
                                            <p class=\"mb-1\">Membership has been ended.</p>
                                            <p class=\"small m-0 text-secondary\">Today, 07:30pm</p>
                                        </div>
                                    </div>
                                </div>
                                <div class=\"dropdown-item\">
                                    <div class=\"d-flex py-2 border-bottom\">
                                        <img src=\"".NIVEL."../vendor/assetsB/images/user2.png\" class=\"img-3x me-3 rounded-3\" alt=\"Admin Dashboards\">
                                        <div class=\"m-0\">
                                            <h6 class=\"mb-1 fw-semibold\">Benjamin Michiels</h6>
                                            <p class=\"mb-1\">Congratulate, James for new job.</p>
                                            <p class=\"small m-0 text-secondary\">Today, 08:00pm</p>
                                        </div>
                                    </div>
                                </div>
                                <div class=\"dropdown-item\">
                                    <div class=\"d-flex py-2\">
                                        <img src=\"".NIVEL."../vendor/assetsB/images/user1.png\" class=\"img-3x me-3 rounded-3\" alt=\"Admin Dashboards\">
                                        <div class=\"m-0\">
                                            <h6 class=\"mb-1 fw-semibold\">Jehovah Roy</h6>
                                            <p class=\"mb-1\">Lewis added new schedule release.</p>
                                            <p class=\"small m-0 text-secondary\">Today, 09:30pm</p>
                                        </div>
                                    </div>
                                </div>
                                <div class=\"d-grid mx-3 my-1\">
                                    <a href=\"javascript:void(0)\" class=\"btn btn-primary\">View all</a>
                                </div>
                            </div>
                        </div>
                        <!--
                        <a href=\"account-settings.html\" data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\" data-bs-custom-class=\"custom-tooltip-blue\" data-bs-title=\"Configuración\">
                            <i class=\"bi bi-gear font-1xx\"></i>
                        </a>
                        -->
                    </div>";

            return $mensajes;
    }

    //---------------------------------------------------
    //-- FUNCIONALIDAD DE LA SECCION DE NOTIFICACIONES --
    //---------------------------------------------------
    function generalNotificaciones($id_usuario){
        $notificaciones = "<ul class=\"updates d-flex align-items-end flex-column overflow-hidden\" id=\"updates\">
                            <li>
                                <a href=\"javascript:void(0)\">
                                    <i class=\"bi bi-envelope-paper text-red font-1x me-2\"></i> <span>9 Mensaje(s)</span>
                                </a>
                            </li>
                            <li>
                                <a href=\"javascript:void(0)\">
                                    <i class=\"bi bi-bar-chart text-blue font-1x me-2\"></i> <span>15 new features updated successfully.</span>
                                </a>
                            </li>
                            <li>
                                <a href=\"javascript:void(0)\">
                                    <i class=\"bi bi-folder-check text-yellow font-1x me-2\"></i> <span>The media folder is created successfully.</span>
                                </a>
                            </li>
                        </ul>";

            return $notificaciones;
    }

    //-------------
    //-- MODALES --
    //-------------
    function modales(){

        //-- MODAL SALIR --
        $modal001 = "<div class=\"modal fade\" id=\"modalSalir\" tabindex=\"-1\" aria-labelledby=\"modalSalirTitle\" aria-hidden=\"true\">".
                    " <div class=\"modal-dialog modal-dialog-centered\">".
                    "  <div class=\"modal-content\">".
                    "   <div class=\"modal-header\">".
                    "    <h5 class=\"modal-title\" id=\"modalSalirTitle\"> <i class=\"bi bi-door-open-fill\"></i> ¿Desea salir del sistema? </h5>".
                    "    <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>".
                    "   </div>".
                    "   <div class=\"modal-body\">".
                    "    Haga clic en 'Cerrar Sesión' para confirmar o en 'Cancelar' para permanecer en el sistema.".
                    "   </div>".
                    "   <div class=\"modal-footer\">".
                    "    <button type=\"button\" class=\"btn btn-info\" onclick=\"cerrarSesion();\">".
                    "     <i class=\"fas fa-sign-out-alt\"></i> Cerrar Sesion".
                    "    </button>".
                    "    <button type=\"button\" class=\"btn btn-secondary\"  data-bs-dismiss=\"modal\" aria-label=\"Close\">".
                    "     <i class=\"fas fa-times\"></i> Cancelar".
                    "    </button>".
                    "   </div>".
                    "  </div>".
                    " </div>".
                    "</div>";

        //-- MODAL PERFIL --
        $modal002 = "<div class=\"modal fade\" id=\"modalPerfil\" tabindex=\"-1\" aria-labelledby=\"modalPerfilTitle\" aria-hidden=\"true\">".
                    " <div class=\"modal-dialog modal-xl modal-dialog-centered\">".
                    "  <div class=\"modal-content\">".
                    "   <div class=\"modal-body p-1\">".
                    "    <iframe width=\"100%\" frameborder=\"0\" height=\"600\" style=\"border:none;\"></iframe>".
                    "   </div>".
                    "  </div>".
                    " </div>".
                    "</div>";

        //-- MODAL LOADER --
        $loader = "<div class=\"modal\" id=\"modalLoader\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\" data-backdrop=\"static\" data-keyboard=\"false\">".
                  " <div class=\"modal-dialog modal-dialog-centered\">".
                  "  <div class=\"modal-content p-1\" style=\"background: rgba(1, 1, 1, 0); border: 0px;\">".
                  "   <h3 class=\"text-light text-center\" >".
                  "    <img src=\"".NIVEL."../vendor/assetsB/images/loader.gif\" width=\"200\"><br>Cargando...".
                  "   </h3>".
                  "  </div>".
                  " </div>".
                  "</div>";

        $modales = $modal001.$modal002.$loader;
        return $modales;
    }
?>
