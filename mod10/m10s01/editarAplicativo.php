<?php
    //-- CONSTANTES --
    define("NIVEL","../../");
    define("ACCION","EDITAR");
    define("APLICACION","100");

    //-- PARAMETROS Y VARIABLES --
    $id = $_GET["id"];

    //-- CONECTA A LA BASE DE DATOS --
    include(NIVEL."../com/config.php");
    $dbAdmin = conectaBD("mysql","admin");

    if($id>0){
        $prepare = $dbAdmin->prepare("SELECT * FROM cat_aplicaciones WHERE id = ?");
        $result = $dbAdmin->execute($prepare,array($id));
        $reg["nombre"] = $result->fields["nombre"];
        $reg["descripcion"] = $result->fields["descripcion"];
        $reg["minutos_sesion"] = $result->fields["minutos_sesion"];
        $reg["link"] = $result->fields["link"];
        $reg["observaciones"] = $result->fields["observaciones"];
        $reg["status"] = $result->fields["status"];
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
                window.parent.tamano(350,"modalAplicativo");

                <?php if($id>0){ ?>
                    $("#status option:selected").removeAttr("selected");
                    $("#status option[value='<?php echo $reg["status"]; ?>']").attr('selected', 'selected');
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
                                    if(res.error === 0) window.location.href = "editarAplicativo.php?id=" + res.id;
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
                <div class="card">
                    <div class="card-body">
                        <form id="formulario" name="formulario" class="row g-3 needs-validation" novalidate>
                            <input type="hidden" id="id" name="id" value="<?= $id ?>" />
                            <input type="hidden" id="accion" name="accion" value="<?= ACCION ?>" />

                            <div class="row gx-3 pt-2">
                                <div class="col-md-3">
                                    <label for="nombre" class="form-label">Nombre (s)</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?= $reg["nombre"] ?>" required />
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Obligatorio</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="descripcion" class="form-label">Descripcion</label>
                                    <input type="text" class="form-control" id="descripcion" name="descripcion" value="<?= $reg["descripcion"] ?>" required />
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Obligatorio</div>
                                </div>
                                <div class="col-md-3">
                                    <label for="minutos_sesion" class="form-label">Sesion (Minutos)</label>
                                    <input type="number" class="form-control" id="minutos_sesion" name="minutos_sesion" value="<?= $reg["minutos_sesion"] ?>" required />
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Obligatorio</div>
                                </div>
                                <div class="col-md-9">
                                    <label for="link" class="form-label">Ruta</label>
                                    <input type="text" class="form-control" id="link" name="link" value="<?= $reg["link"] ?>" required />
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Obligatorio</div>
                                </div>
                                <div class="col-md-3">
                                    <label for="status" class="form-label">Estatus</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option selected disabled value="">Seleccione</option>
                                        <option value="1">Activo</option>
                                        <option value="0">Inactivo</option>
                                    </select>
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Obligatorio</div>
                                </div>
                                <div class="col-md-12">
                                    <label for="observaciones" class="form-label">Observaciones</label>
                                    <textarea class="form-control" id="observaciones" name="observaciones" rows="3"><?= $reg["observaciones"] ?></textarea>
                                </div>

                            </div>
                            <div class="col-12 text-center pt-2">
                                <div class="btn btn-sm btn-primary" id="enviar" >
                                    <i class="bi bi-floppy2"></i> Guardar
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
</html>