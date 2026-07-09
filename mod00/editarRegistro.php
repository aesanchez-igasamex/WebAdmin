<?php
    //-- CONSTANTES --
    define("NIVEL","../");

    //-- PARAMETROS Y VARIABLES --
    $id = $_GET["id"];

    //-- CONECTA A LA BASE DE DATOS --
    include(NIVEL."com/configBD.php");

    if($id>0){
        $sql = "SELECT id, nombre, ap_paterno, ap_materno, correo, rol, area, status
                FROM tbl_usuarios";
        $db -> SetFetchMode(ADODB_FETCH_NUM);
        $result = $db->execute($sql);
        $reg["id"] = $result->fields[0];
        $reg["nombre"] = $result->fields[1];
        $reg["ap_paterno"] = $result->fields[2];
        $reg["ap_materno"] = $result->fields[3];
        $reg["correo"] = $result->fields[4];
        $reg["rol"] = $result->fields[5];
        $reg["area"] = $result->fields[6];
        $reg["status"] = $result->fields[7];
    }

    
    $cont=1;
    $db -> SetFetchMode(ADODB_FETCH_ASSOC);
    $result = $db->execute("SELECT * FROM cat_roles WHERE status = 1");
    while(!$result->EOF){
        $catRoles[$cont]["id"] = $result->fields["id"];
        $catRoles[$cont]["nombre"] = $result->fields["nombre"];
        $result->MoveNext();
        $cont++;
    }

    $cont=1;
    $db -> SetFetchMode(ADODB_FETCH_ASSOC);
    $result = $db->execute("SELECT * FROM cat_areas WHERE status = 1");
    while(!$result->EOF){
        $catAreas[$cont]["id"] = $result->fields["id"];
        $catAreas[$cont]["nombre"] = $result->fields["nombre"];
        $result->MoveNext();
        $cont++;
    }

    $cont=1;
    $db -> SetFetchMode(ADODB_FETCH_ASSOC);
    $result = $db->execute("SELECT * FROM cat_status_usuarios WHERE status = 1");
    while(!$result->EOF){
        $catStatus[$cont]["id"] = $result->fields["id"];
        $catStatus[$cont]["nombre"] = $result->fields["nombre"];
        $result->MoveNext();
        $cont++;
    }


    //-- CIERRA CONEXION A LA BASE DE DATOS --
    $db->close();
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Editar registro</title>

        <!-- CSS -->
        <link rel="stylesheet" href="<?= NIVEL ?>assets/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?= NIVEL ?>assets/fonts/bootstrap/bootstrap-icons.css">
        <link rel="stylesheet" href="<?= NIVEL ?>assets/css/main.min.css">
        <link rel="stylesheet" href="<?= NIVEL ?>assets/vendor/overlay-scroll/OverlayScrollbars.min.css">

        <!-- JAVASCRIPT -->
        <script src="<?= NIVEL ?>assets/js/jquery.min.js"></script>
        <script src="<?= NIVEL ?>assets/js/bootstrap.bundle.min.js"></script>
        <script src="<?= NIVEL ?>assets/js/modernizr.js"></script>
        <script src="<?= NIVEL ?>assets/js/moment.js"></script>
        <script type="text/javascript">
            $(document).ready(function(){
                window.parent.tamano(400);
                <? if($id==0){ ?>
                    $(".actualizar").hide();
                    $("#usuario").attr("required","required");
                    $("#contrasena").attr("required","required");
                    <? } ?>
                <? if($id>0){ ?>
                    $("#rol option:selected").removeAttr("selected");
                    $("#rol option[value='<?php echo $reg["rol"]; ?>']").attr('selected', 'selected');
                    $("#area option:selected").removeAttr("selected");
                    $("#area option[value='<?php echo $reg["rol"]; ?>']").attr('selected', 'selected');
                    $("#status option:selected").removeAttr("selected");
                    $("#status option[value='<?php echo $reg["status"]; ?>']").attr('selected', 'selected');
                    $(".credenciales").hide();
                    $("#actualiza").change(function(){
                        if ($(this).is(":checked") ) { 
                            $(".credenciales").show(); 
                            $("#usuario").attr("required","required");
                            $("#contrasena").attr("required","required");
                        }
                        else { 
                            $(".credenciales").hide(); 
                            $("#contrasena").val("");
                            $("#usuario").removeAttr("required");
                            $("#contrasena").removeAttr("required");
                        }
                    });
                <? } ?>

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
                                url: "guardarRegistro.php",
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
                            <div class="col-md-4">
                                <label for="nombre" class="form-label">Nombre (s)</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" value="<?= $reg["nombre"] ?>" required />
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">Obligatorio</div>
                            </div>
                            <div class="col-md-4">
                                <label for="ap_paterno" class="form-label">Apellido Paterno</label>
                                <input type="text" class="form-control" id="ap_paterno" name="ap_paterno" value="<?= $reg["ap_paterno"] ?>" required />
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">Obligatorio</div>
                            </div>
                            <div class="col-md-4">
                                <label for="ap_materno" class="form-label">Apellido Materno</label>
                                <input type="text" class="form-control" id="ap_materno" name="ap_materno" value="<?= $reg["ap_materno"] ?>" required />
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">Obligatorio</div>
                            </div>

                            <div class="col-md-4">
                                <label for="correo" class="form-label">E-mail</label>
                                <input type="email" class="form-control" id="correo" name="correo" value="<?= $reg["correo"] ?>" required />
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">Obligatorio</div>
                            </div>
                            <div class="col-md-3">
                                <label for="rol" class="form-label">Rol</label>
                                <select class="form-select" id="rol" name="rol" required>
                                    <option selected disabled value="">Seleccione</option>
                                    <? for($x=1;$x<=count($catRoles);$x++){ ?>
                                        <option value="<?= $catRoles[$x]["id"] ?>"><?= $catRoles[$x]["nombre"] ?></option>
                                    <? } ?>
                                </select>
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">Rol no valido</div>
                            </div>
                            <div class="col-md-3">
                                <label for="area" class="form-label">Area</label>
                                <select class="form-select" id="area" name="area" required>
                                    <option selected disabled value="">Seleccione</option>
                                    <? for($x=1;$x<=count($catAreas);$x++){ ?>
                                        <option value="<?= $catAreas[$x]["id"] ?>"><?= $catAreas[$x]["nombre"] ?></option>
                                    <? } ?>
                                </select>
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">Area no valida</div>
                            </div>
                            <div class="col-md-2">
                                <label for="status" class="form-label">Estatus</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option selected disabled value="">Seleccione</option>
                                    <? for($x=1;$x<=count($catStatus);$x++){ ?>
                                        <option value="<?= $catStatus[$x]["id"] ?>"><?= $catStatus[$x]["nombre"] ?></option>
                                    <? } ?>
                                </select>
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">Area no valida</div>
                            </div>


                            <div class="col-md-4 actualizar"></div>
                            <div class="col-md-4 actualizar text-center">
                                <div class="form-check pb-0 mb-0">
                                    <input class="form-check-input" type="checkbox" value="1" id="actualiza" name="actualiza" />
                                    <label for="actualiza" class="form-label"> Actualizar Contraseña</label>
                                </div>
                            </div>
                            <div class="col-md-4 actualizar"></div>

                            <div class="col-md-2 credenciales"></div>
                            <div class="col-md-4 credenciales">
                                <label for="usuario" class="form-label">Usuario</label>
                                <input type="text" class="form-control" id="usuario" name="usuario" value="<?= $reg["usuario"] ?>"/>
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">Obligatorio</div>
                            </div>
                            <div class="col-md-4 credenciales">
                                <label for="contrasena" class="form-label">Contraseña</label>
                                <input type="text" class="form-control" id="contrasena" name="contrasena"/>
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">Obligatorio</div>
                            </div>
                            <div class="col-md-2 credenciales"></div>

                            <div class="col-12 text-center">
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