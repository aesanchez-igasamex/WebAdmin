<?php
    //-- CONSTANTES --
    define("NIVEL","../../");
    define("ACCION","ASIGNAR_USUARIOS");

    //-- PARAMETROS Y VARIABLES --
    $id_aplicacion = ($_GET["id_aplicacion"]>0)? $_GET["id_aplicacion"] : 0 ;
    $usuarios = array();

    //-- CONECTA A LA BASE DE DATOS --
    include(NIVEL."../com/configBD_Admin.php");

    //-- OBTIENE USUARIOS QUE NO PERTENECEN A LA APLICACION --
    $cont = 1;
    $sql = "SELECT id, nombre, ap_paterno, ap_materno
            FROM tbl_usuarios
            WHERE id NOT IN (SELECT id_usuario FROM tbl_usuarios_aplicacion WHERE id_aplicacion = ?)
            ORDER BY nombre ASC";
    $prepare = $dbAdmin->prepare($sql);
    $result = $dbAdmin->execute($prepare, [$id_aplicacion]);
    while(!$result->EOF){
        $usuarios[$cont]["id"] = $result->fields["id"];
        $usuarios[$cont]["nombre"] = $result->fields["nombre"]." ".$result->fields["ap_paterno"]." ".$result->fields["ap_materno"];
        $cont++;
        $result->MoveNext();
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
        <title>Asignar usuarios</title>
        <?php include(NIVEL."com/dependenciesUP.php"); ?>
        <style> body { overflow-x: hidden; } </style>
        <script type="text/javascript">
            $(document).ready(function(){
                window.parent.tamano(500, "modalAsignarUsuarios");

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
                            <input type="hidden" id="id_aplicacion" name="id_aplicacion" value="<?= $id_aplicacion ?>" />
                            <input type="hidden" id="accion" name="accion" value="<?= ACCION ?>" />
                            <div class="row">

                                <div class="col-md-12">
                                    <label for="usuarios" class="form-label">Usuarios</label>
                                    <select id="usuarios" name="usuarios[]" class="form-select" multiple size="12" required>
                                        <?php for($x=1;$x<=count($usuarios);$x++){ ?>
                                            <option value="<?= $usuarios[$x]["id"] ?>"><?= $usuarios[$x]["nombre"] ?></option>
                                        <?php } ?>
                                    </select>
                                    <div class="small pl-2">Seleccion multiple: Ctrl + Click</div>
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