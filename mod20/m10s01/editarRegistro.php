<?php
    //-- CONSTANTES --
    define("NIVEL","../../");
    define("ACCION","EDITAR");

    //-- PARAMETROS Y VARIABLES --
    $id = ($_GET["id"]>0) ? $_GET["id"] : 0;
    $reg = $params = array();

    //-- CONECTA A LA BASE DE DATOS --
    include(NIVEL."../com/configBD_Admin.php");

    if($id>0){
        $params[] = $id;
        $prepare = $dbAdmin->prepare("SELECT * FROM cat_actividad_regulada WHERE id = ?");
        $result = $dbAdmin->execute($prepare,$params);
        $reg["clave"] = $result->fields["clave"];
        $reg["nombre"] = $result->fields["nombre"];
        $reg["abreviatura"] = $result->fields["abreviatura"];
        $reg["status"] = $result->fields["status"];
    }

    //-- CIERRA CONEXION A LA BASE DE DATOS --
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
        <script type="text/javascript">
            $(document).ready(function(){
                window.parent.tamano(400);

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
                            success: function(resultado) {
                                console.log(resultado);
                                $(window.parent.document.body).loadingModal("destroy");
                                mensaje(JSON.parse(resultado));
                            },
                            error: function( xhr, err ) {
                                $(window.parent.document.body).loadingModal("destroy");
                                alert("Error " + err);
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

                            <div class="row gx-3">
                                <div class="col-xxl-12">
                                    <div class="form-section-title p-1 mt-3 mb-2 fw-bold text-center">General</div>
                                </div>
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
                            </div>

                            <div class="row gx-3">
                                <div class="col-xxl-12">
                                    <div class="form-section-title p-1 mt-3 mb-2 fw-bold text-center">Base de Datos</div>
                                </div>
                                <div class="col-md-2">
                                    <label for="servidor" class="form-label">Servidor</label>
                                    <input type="text" class="form-control" id="servidor" name="servidor" value="<?= $reg["servidor"] ?>" required />
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Obligatorio</div>
                                </div>
                                <div class="col-md-2">
                                    <label for="tipo_conexion" class="form-label">Tipo</label>
                                    <select class="form-select" id="tipo_conexion" name="tipo_conexion" required>
                                        <option selected disabled value="">Seleccione</option>
                                        <option value="MySQL">MySQL</option>
                                        <option value="MSSQL">MSSQL</option>
                                    </select>
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Obligatorio</div>
                                </div>
                                <div class="col-md-2">
                                    <label for="bd" class="form-label">Base de Datos</label>
                                    <input type="text" class="form-control" id="bd" name="bd" value="<?= $reg["bd"] ?>" required />
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Obligatorio</div>
                                </div>
                                <div class="col-md-2">
                                    <label for="usuario_bd" class="form-label">Usuario</label>
                                    <input type="text" class="form-control" id="usuario_bd" name="usuario_bd" value="<?= $reg["usuario_bd"] ?>" required />
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Obligatorio</div>
                                </div>
                                <div class="col-md-2">
                                    <label for="password_bd" class="form-label">Contraseña</label>
                                    <input type="text" class="form-control" id="password_bd" name="password_bd" value="<?= $reg["password_bd"] ?>" required />
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Obligatorio</div>
                                </div>
                                <div class="col-md-2">
                                    <label for="historial" class="form-label">Historial</label>
                                    <input type="text" class="form-control" id="historial" name="historial" value="<?= $reg["historial"] ?>" />
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Obligatorio</div>
                                </div>
                            </div>

                            <div class="col-12 text-center">
                                <div class="btn btn-primary" id="enviar" >
                                    <i class="bi bi-check-circle-fill"></i> Guardar
                                </div>
                                <div class="btn btn-secondary" onclick="window.parent.recargar();" >
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