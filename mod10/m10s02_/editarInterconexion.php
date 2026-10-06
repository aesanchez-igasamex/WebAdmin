<?php
    //-- CONSTANTES --
    define("NIVEL","../");

    //-- PARAMETROS Y VARIABLES --
    $id = $_GET["id"];

    //-- CONECTA A LA BASE DE DATOS --
    include(NIVEL."com/configBD_SAG.php");


    //-- OBTIENE LA INFORMACION DEL EMR --
    if($id > 0){
        $params = array($id);
        $prepare = $dbSAG->prepare("SELECT * FROM clientes_emr WHERE id = ?");
        $result = $dbSAG->execute($prepare,$params);
        $reg["id"] = $result->fields["id"];
        $reg["id_emr"] = $result->fields["id_emr"];
        $reg["nombre"] = $result->fields["nombre"];
        $reg["sociedad_mercantil"] = $result->fields["sociedad_mercantil"];
        $reg["preciso"] = $result->fields["preciso"];
        $reg["permiso"] = $result->fields["permiso"];
        $reg["actividad_regulada"] = $result->fields["actividad_regulada"];
        $reg["zona"] = $result->fieldsv["zona"];
        $reg["estado"] = $result->fields["estado"];
    }


    //-- CATALOGO DE SOCIUEDAD MERCANTIL --
    $cont = 1;
    $params = array(1);
    $prepare = $dbSAG->prepare("SELECT * FROM cat_sociedad_mercantil WHERE status = ? ORDER BY nombre ASC");
    $result = $dbSAG->execute($prepare,$params);
    while(!$result->EOF){
        $cat_sociedad_mercantil[$cont]["id"] = $result->fields["id"];
        $cat_sociedad_mercantil[$cont]["nombre"] = $result->fields["nombre"];
        $result->MoveNext();
        $cont++;
    }

    //-- CIERRA CONEXION A LA BASE DE DATOS --
    if($result) $result->close();
    $dbSAG->close();
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
                window.parent.tamano(600,"modalInterconexion");
                <?php if($id==0){ ?>
                    $(".actualizar").hide();
                    $("#usuario").attr("required","required");
                    $("#contrasena").attr("required","required");
                    <?php } ?>
                <?php if($id>0){ ?>
                    $("#sociedad_mercantil option:selected").removeAttr("selected");
                    $("#sociedad_mercantil option[value='<?= $reg["sociedad_mercantil"] ?>']").attr('selected', 'selected');
                <?php } ?>

                const forms = document.querySelectorAll(".needs-validation");
                Array.from(forms).forEach((form) => {
                    form.addEventListener("submit", (event) => {
                        if (!form.checkValidity()) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        else{
                            campos = new FormData(form);
                            $.ajax({
                                type: "POST",
                                url: "controller.php",
                                contentType: false,
                                data: campos,
                                processData:false,
                                success: function(msg) {
                                    (msg.length>0)? alert(msg) : location.reload() ;
                                },
                                error: function( xhr, err ) {
                                    alert('Error'+err);
                                }
                            });
                        }
                        form.classList.add("was-validated");
                    }, false );
                });

                $("#expandAll").click(function(){ $(".accordion-collapse").collapse('hide'); $(".accordion-collapse").collapse('show'); });
                $("#collapseAll").click(function(){ $(".accordion-collapse").collapse('hide'); });

            });
        </script>

    </head>

    <body>
        <div class="row gx-3">
            <div class="col-xxl-12">
                <div class="card">
                    <div class="card-body">
                        <form id="formulario"  class="row g-3 needs-validation" novalidate>
                            <input type="hidden" id="id" name="id" value="<?= $id ?>" />
                            <input type="hidden" id="accion" name="accion" value="<?= $accion ?>" />

                            <div class="accordion" id="acordeon">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="acordeon01">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAcordeon01" aria-expanded="true" aria-controls="collapseAcordeon01">
                                            Información General
                                        </button>
                                    </h2>
                                    <div id="collapseAcordeon01" class="accordion-collapse collapse show" aria-labelledby="acordeon01" data-bs-parent="#acordeon">
                                        <div class="accordion-body">
                                            <div class="row gx-3">
                                                <div class="col-4">
                                                    <label for="nombre" class="form-label">Nombre</label>
                                                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?= $reg["nombre"] ?>" required />
                                                    <div class="valid-feedback">Correcto</div>
                                                    <div class="invalid-feedback">Obligatorio</div>
                                                </div>
                                                <div class="col-3">
                                                    <label for="sociedad_mercantil" class="form-label">Sociedad Mercantil</label>
                                                    <select class="form-select" id="sociedad_mercantil" name="sociedad_mercantil" required>
                                                        <option selected disabled value="">Seleccione</option>
                                                        <?php for($x=1;$x<=count($cat_sociedad_mercantil);$x++){ ?>
                                                            <option value="<?= $cat_sociedad_mercantil[$x]["id"] ?>"><?= $cat_sociedad_mercantil[$x]["nombre"] ?></option>
                                                        <?php } ?>
                                                    </select>
                                                    <div class="valid-feedback">Correcto</div>
                                                    <div class="invalid-feedback">Obligatorio</div>
                                                </div>
                                                <div class="col-3">
                                                    <label for="preciso" class="form-label">Ubicación precisa</label>
                                                    <input type="text" class="form-control" id="preciso" name="preciso" value="<?= $reg["preciso"] ?>" required />
                                                    <div class="valid-feedback">Correcto</div>
                                                    <div class="invalid-feedback">Obligatorio</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="acordeon02">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAcordeon02" aria-expanded="false" aria-controls="collapseAcordeon02">
                                            Seccion 02
                                        </button>
                                    </h2>
                                    <div id="collapseAcordeon02" class="accordion-collapse collapse" aria-labelledby="acordeon02" data-bs-parent="#acordeon">
                                        <div class="accordion-body">
                                            <div class="row gx-3">

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="acordeon03">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAcordeon03" aria-expanded="false" aria-controls="collapseAcordeon03">
                                        Seccion 03
                                    </button>
                                    </h2>
                                    <div id="collapseAcordeon03" class="accordion-collapse collapse" aria-labelledby="acordeon03" data-bs-parent="#acordeon">
                                        <div class="accordion-body">

                                        </div>
                                </div>
                            </div>

                            <div class="col-12 text-center pt-3">
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-check-circle-fill"></i> Guardar
                                </button>
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