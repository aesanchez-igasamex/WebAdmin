<?php
    //-- CONSTANTES --
    define("NIVEL","../../");
    define("ACCION","PRIVILEGIOS");

    //-- PARAMETROS Y VARIABLES --
    $id_usuario = (isset($_REQUEST["id_usuario"])) ? $_REQUEST["id_usuario"] : 0 ;
    $id_aplicacion = ($_GET["id_aplicacion"]>0)? $_GET["id_aplicacion"] : 0 ;
    $menu = $catMenu = array();


    //-- CONECTA A LA BASE DE DATOS --
    include(NIVEL."../com/configBD_Admin.php");

    //-- OBTIENE MENUS ASIGNADOS AL USUARIO --
    $cont = 1;
    $prepare = $dbAdmin->prepare("SELECT id_menu FROM tbl_usuarios_menu WHERE id_usuario = ? AND id_menu IN (SELECT id FROM tbl_menu WHERE sistema = ? AND status = ?)");
    $result = $dbAdmin->execute($prepare, [$id_usuario, $id_aplicacion, 1]);
    while(!$result->EOF){
        $menu[$cont]["id"] = $result->fields[0];
        $result->MoveNext();
        $cont++;
    }

    //-- OBTIENE CATALOGO DE MENUS --
    $cont = 1;
    $prepare = $dbAdmin->prepare("SELECT id, icono, nombre, padre FROM tbl_menu WHERE status = ? AND sistema = ? ORDER BY orden ASC");
    $result = $dbAdmin->execute($prepare, [1, $id_aplicacion]);
    while(!$result->EOF){
        $catMenu[$cont]["id"] = $result->fields[0];
        $catMenu[$cont]["nombre"] = $result->fields[1]." ".$result->fields[2];
        $catMenu[$cont]["submenu"] = ($result->fields[3]>0)? true : false ;
        $result->MoveNext();
        $cont++;
    }

    //-- CIERRA CONEXION A LA BASE DE DATOS --
    if($result) $result->Close();
    $dbAdmin->close();
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Editar permisos</title>
        <?php include(NIVEL."com/dependenciesUP.php"); ?>
        <style> body { overflow-x: hidden; } </style>
        <script type="text/javascript">
            $(document).ready(function(){
                window.parent.tamano(350, "modalPermisos");

                <?php for($m=1;$m<=count($menu);$m++){ ?>
                    $("#menus<?php echo $menu[$m]["id"]; ?>").prop('checked', true);
                <?php } ?>


                $("#enviar").click(function(event){
                    if($("#formulario")[0].checkValidity()) {
                        $(window.parent.document.body).loadingModal({ text: "Cargando datos, espere...", animation: "cubeGrid" });
                        campos = new FormData($("#formulario")[0]);
                        $.ajax({
                            type: "POST",
                            url: "controller.php",
                            contentType: false,
                            data: campos,
                            processData: false,
                            success: function(res) {
                                $(window.parent.document.body).loadingModal("destroy");
                                try {
                                    console.log(res);
                                    res = JSON.parse(res);
                                    window.parent.mensaje(res);
                                    if(res.error === 0) window.parent.recargar();
                                }
                                catch (e) {
                                    console.error("Error al parsear JSON:", e);
                                    window.parent.mensaje({error: 1, titulo: "Error", mensaje: "No fue posible procesar la respuesta del servidor.", aceptar: 1});
                                }
                            },
                            error: function( xhr, err ) {
                                $(window.parent.document.body).loadingModal("destroy");
                                window.parent.mensaje({error: 1, titulo: "Error", mensaje: "No fue posible establecer comunicación con el servidor.", aceptar: 1});
                            }
                        });
                    }
                    else{
                        event.stopPropagation();
                    }
                    $("#formulario")[0].classList.add("was-validated");
                });
            });
        </script>

    </head>

    <body>
        <div class="row gx-3">
            <div class="col-xxl-12">
                <div class="card pt-3">
                    <div class="card-body">
                        <form id="formulario" name="formulario" class="row g-3 needs-validation" novalidate>
                            <input type="hidden" id="id_usuario" name="id_usuario" value="<?= $id_usuario ?>" />
                            <input type="hidden" id="id_aplicacion" name="id_aplicacion" value="<?= $id_aplicacion ?>" />
                            <input type="hidden" id="accion" name="accion" value="<?= ACCION ?>" />
                            <div class="row">

                                <div class="col-md-12">
                                    <?php for($i=1;$i<=count($catMenu);$i++){ ?>
                                        <div class="form-check-sm <?= ($catMenu[$i]["submenu"]) ? "ps-4" : "" ?>">
                                            <input class="form-check-input" type="checkbox" name="menus[]" id="menus<?= $catMenu[$i]["id"];?>" value="<?= $catMenu[$i]["id"];?>"> <span class="<?= ($catMenu[$i]["submenu"]) ? "" : "fw-bold" ?>"><?= $catMenu[$i]["nombre"];?></span>
                                        </div>
                                    <?php } ?>
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Obligatorio</div>
                                </div>

                            </div>

                            <div class="col-12 text-center pt-2 pb-2">
                                <div class="btn btn-sm btn-primary" id="enviar" >
                                    <i class="bi bi-check-circle-fill"></i> Guardar
                                </div>
                                <div class="btn btn-sm btn-secondary" onclick="window.parent.recargar();" >
                                    <i class="bi bi-x-lg"></i> Cerrar
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </body>
<body>