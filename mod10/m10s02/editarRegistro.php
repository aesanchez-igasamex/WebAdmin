<?php
    //-- CONSTANTES --
    define("NIVEL","../../");
    define("ACCION","EDITAR");
    define("APLICACION","100");

    //-- PARAMETROS Y VARIABLES --
    $id = $_GET["id"];
    $reg = $catSociedadMercantil = array();

    //-- CONECTA A LA BASE DE DATOS --
    include(NIVEL."../com/config.php");
    $dbAdmin = conectaBD("mysql","admin");

    if($id>0){
        $prepare = $dbAdmin->prepare("SELECT * FROM tbl_permisionarios WHERE id = ?");
        $result = $dbAdmin->execute($prepare,array($id));
        $reg["razon_social"] = $result->fields["razon_social"];
        $reg["preciso"] = $result->fields["preciso"];
        $reg["rfc"] = $result->fields["rfc"];
        $reg["id_sociedad_mercantil"] = $result->fields["id_sociedad_mercantil"];
        $reg["id_actividad_regulada"] = $result->fields["id_actividad_regulada"];
        $reg["permiso"] = $result->fields["permiso"];
        $reg["id_comercializadora"] = $result->fields["id_comercializadora"];
        $reg["operacion_ini"] = $result->fields["operacion_ini"];
        $reg["operacion_fin"] = $result->fields["operacion_fin"];
        $reg["status"] = $result->fields["status"];
    }

    //-- CATÁLOGO DE SOCIEDADES MERCANTILES --
    $cont = 1;
    $prepare = $dbAdmin->prepare("SELECT * FROM cat_sociedad_mercantil WHERE status = ? ORDER BY nombre ASC");
    $result = $dbAdmin->execute($prepare,[1]);
    while(!$result->EOF){
        $catSociedadMercantil[$cont]["id"] = $result->fields["id"];
        $catSociedadMercantil[$cont]["nombre"] = $result->fields["nombre"];
        $result->MoveNext();
        $cont++;
    }

    //-- CATÁLOGO DE ACTIVIDADES REGULADAS --
    $cont = 1;
    $prepare = $dbAdmin->prepare("SELECT * FROM cat_actividad_regulada WHERE status = ? ORDER BY nombre ASC");
    $result = $dbAdmin->execute($prepare,[1]);
    while(!$result->EOF){
        $catActividadRegulada[$cont]["id"] = $result->fields["id"];
        $catActividadRegulada[$cont]["nombre"] = $result->fields["clave"] . " - " . $result->fields["nombre"];
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
                window.parent.tamano(325,"modalRegistro");

                <?php if($id>0){ ?>
                    $("#id_sociedad_mercantil option:selected").removeAttr("selected");
                    $("#id_sociedad_mercantil option[value='<?php echo $reg["id_sociedad_mercantil"]; ?>']").attr('selected', 'selected');
                    $("#id_actividad_regulada option:selected").removeAttr("selected");
                    $("#id_actividad_regulada option[value='<?php echo $reg["id_actividad_regulada"]; ?>']").attr('selected', 'selected');
                    $("#id_comercializadora option:selected").removeAttr("selected");
                    $("#id_comercializadora option[value='<?php echo $reg["id_comercializadora"]; ?>']").attr('selected', 'selected');
                    $("#status option:selected").removeAttr("selected");
                    $("#status option[value='<?php echo $reg["status"]; ?>']").attr('selected', 'selected');
                    <?php if($reg["id_actividad_regulada"]==1) echo "$(\"#id_comercializadora\").attr(\"disabled\", \"disabled\");"; ?>
                <?php } ?>

                $("#id_actividad_regulada").change(function(){
                    ($(this).val()==1)? $("#id_comercializadora").attr("disabled", "disabled") : $("#id_comercializadora").removeAttr("disabled") ;
                });

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
                                    if(res.error === 0) window.location.href = "editarRegistro.php?id=" + res.id;
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

                            <div class="row gx-2 pt-2">
                                <div class="col-5">
                                    <label for="razon_social" class="form-label">Razon Social</label>
                                    <input type="text" class="form-control" id="razon_social" name="razon_social" value="<?= $reg["razon_social"] ?>" required />
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Obligatorio</div>
                                </div>
                                <div class="col-4">
                                    <label for="preciso" class="form-label">Anotacion</label>
                                    <input type="text" class="form-control" id="preciso" name="preciso" value="<?= $reg["preciso"] ?>" />
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Obligatorio</div>
                                </div>
                                <div class="col-3">
                                    <label for="id_sociedad_mercantil" class="form-label">Sociedad Mercantil</label>
                                    <select class="form-select" id="id_sociedad_mercantil" name="id_sociedad_mercantil" required>
                                        <option selected disabled value="">Seleccione</option>
                                        <?php for($x=1;$x<=count($catSociedadMercantil);$x++){ ?>
                                            <option value="<?= $catSociedadMercantil[$x]["id"] ?>"><?= $catSociedadMercantil[$x]["nombre"] ?></option>
                                        <?php } ?>
                                    </select>
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Obligatorio</div>
                                </div>
                            </div>
                            <div class="row gx-2 pt-2">
                                <div class="col-3">
                                    <label for="rfc" class="form-label">RFC</label>
                                    <input type="text" class="form-control" id="rfc" name="rfc" value="<?= $reg["rfc"] ?>" minlength="12" maxlength="13" required/>
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Obligatorio</div>
                                </div>
                                <div class="col-5">
                                    <label for="id_actividad_regulada" class="form-label">Actividad Regulada</label>
                                    <select class="form-select" id="id_actividad_regulada" name="id_actividad_regulada" required>
                                        <option selected disabled value="">Seleccione</option>
                                        <?php for($x=1;$x<=count($catActividadRegulada);$x++){ ?>
                                            <option value="<?= $catActividadRegulada[$x]["id"] ?>"><?= $catActividadRegulada[$x]["nombre"] ?></option>
                                        <?php } ?>
                                    </select>
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Obligatorio</div>
                                </div>
                                <div class="col-4">
                                    <label for="permiso" class="form-label">Permiso</label>
                                    <input type="text" class="form-control" id="permiso" name="permiso" value="<?= $reg["permiso"] ?>" required />
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Obligatorio</div>
                                </div>
                            </div>
                            <div class="row gx-2 pt-2">
                                <div class="col-3">
                                    <label for="id_comercializadora" class="form-label">Comercializadora</label>
                                    <select class="form-select" id="id_comercializadora" name="id_comercializadora" required>
                                        <option selected disabled value="">Seleccione</option>
                                        <?php for($x=1;$x<=count($catComercializadora);$x++){ ?>
                                            <option value="<?= $catComercializadora[$x]["id"] ?>"><?= $catComercializadora[$x]["nombre"] ?></option>
                                        <?php } ?>
                                    </select>
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Obligatorio</div>
                                </div>
                                <div class="col-3">
                                    <label for="operacion_ini" class="form-label">Inicio de Operacion</label>
                                    <input type="date" class="form-control" id="operacion_ini" name="operacion_ini" value="<?= $reg["operacion_ini"] ?>" required />
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Obligatorio</div>
                                </div>
                                <div class="col-3">
                                    <label for="operacion_fin" class="form-label">Fin de Operacion</label>
                                    <input type="date" class="form-control" id="operacion_fin" name="operacion_fin" value="<?= $reg["operacion_fin"] ?>" />
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Obligatorio</div>
                                </div>
                                <div class="col-3">
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