<?php
    //-- CONSTANTES --
    define("NIVEL","../../");
    define("ACCION","USUARIO");

    //-- PARAMETROS Y VARIABLES --
    $id = ($_GET["id"]>0)? $_GET["id"] : 0 ;
    $id_aplicacion = ($_GET["id_aplicacion"]>0)? $_GET["id_aplicacion"] : 0 ;
    $usuario = $catStatus = $filtroStatus = $catRoles = $catAreas = array();
    $usuario["status"] = 1;
    $usuario["foto"] = "default.png";
    $filtroStatus = [2,3];

    //-- CONECTA A LA BASE DE DATOS --
    include(NIVEL."../com/config.php");
    $dbAdmin = conectaBD("mysql","admin");

    if($id>0){
        $prepare = $dbAdmin->prepare("SELECT * FROM tbl_usuarios WHERE id = ?");
        $result = $dbAdmin->execute($prepare,[$id]);
        $usuario["nombre"] = $result->fields["nombre"];
        $usuario["ap_paterno"] = $result->fields["ap_paterno"];
        $usuario["ap_materno"] = $result->fields["ap_materno"];
        $usuario["correo"] = $result->fields["correo"];
        $usuario["foto"] = (strlen($result->fields["foto"])>0)? $result->fields["foto"] : "default.png";
        $usuario["status"] = $result->fields["status"];
        if($usuario["status"]==0) $filtroStatus = [0,3];
        if($usuario["status"]==1) $filtroStatus = [0,1,3];
        if($usuario["status"]==2) $filtroStatus = [0,2,3];
        if($usuario["status"]==3) $filtroStatus = [0,2,3];

        if($id_aplicacion>0){
            $prepare = $dbAdmin->prepare("SELECT a.id_rol FROM tbl_usuarios_rol AS a JOIN cat_roles AS b ON a.id_rol = b.id WHERE a.id_usuario = ? AND b.aplicacion = ?");
            $result = $dbAdmin->execute($prepare,[$id, $id_aplicacion]);
            $usuario["rol"] = ($result->fields[0]>0)? $result->fields[0] : 0 ;

            $prepare = $dbAdmin->prepare("SELECT a.id_area FROM tbl_usuarios_area AS a JOIN cat_areas AS b ON a.id_area = b.id WHERE a.id_usuario = ? AND b.aplicacion = ?");
            $result = $dbAdmin->execute($prepare,[$id, $id_aplicacion]);
            $usuario["area"] = ($result->fields[0]>0)? $result->fields[0] : 0 ;
        }
    }

    //-- CATALOGO DE ESTATUS --
    $cont = 1;
    $placeholders = implode(',', array_fill(0, count($filtroStatus), '?'));
    $params[] = 1;
    $params = array_merge($params, $filtroStatus);
    $sql = "SELECT * FROM cat_status_usuario WHERE status = ? AND id IN ($placeholders)";
    $prepare = $dbAdmin->prepare($sql);
    $result = $dbAdmin->execute($prepare, $params);
    while(!$result->EOF){
        $catStatus[$cont]["id"] = $result->fields["id"];
        $catStatus[$cont]["nombre"] = $result->fields["nombre"];
        $result->MoveNext();
        $cont++;
    }

    if($id_aplicacion>0){

        //-- CATALOGO DE ROLES --
        $cont = 1;
        $prepare = $dbAdmin->prepare("SELECT * FROM cat_roles WHERE aplicacion = ? AND status = ?");
        $result = $dbAdmin->execute($prepare, [$id_aplicacion, 1]);
        while(!$result->EOF){
            $catRoles[$cont]["id"] = $result->fields["id"];
            $catRoles[$cont]["nombre"] = $result->fields["nombre"];
            $result->MoveNext();
            $cont++;
        }

        //-- CATALOGO DE AREAS --
        $cont = 1;
        $sql = "SELECT * FROM cat_areas WHERE aplicacion = ? AND status = ?";
        $prepare = $dbAdmin->prepare($sql);
        $result = $dbAdmin->execute($prepare, [$id_aplicacion, 1]);
        while(!$result->EOF){
            $catAreas[$cont]["id"] = $result->fields["id"];
            $catAreas[$cont]["nombre"] = $result->fields["nombre"];
            $result->MoveNext();
            $cont++;
        }
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
        <title>Editar usuario</title>
        <?php include(NIVEL."com/dependenciesUP.php"); ?>
        <style> body { overflow-x: hidden; } </style>
        <script type="text/javascript">
            $(document).ready(function(){
                window.parent.tamano(300, "modalUsuario");

                <?php if($id>0){ ?>
                    $("#status option:selected").removeAttr("selected");
                    $("#status option[value='<?= $usuario["status"] ?>']").attr('selected', 'selected');
                    <?php if($id_aplicacion>0){ ?>
                        $("#rol option:selected").removeAttr("selected");
                        $("#rol option[value='<?= $usuario["rol"] ?>']").attr('selected', 'selected');
                        $("#area option:selected").removeAttr("selected");
                        $("#area option[value='<?= $usuario["area"] ?>']").attr('selected', 'selected');
                    <?php } ?>
                <?php } ?>

                $("#imagen").change(function(){
                    var file = this.files[0];
                    if(file){
                        var reader = new FileReader();
                        reader.onload = function(e){ $("#preview").attr("src", e.target.result).show(); };
                        reader.readAsDataURL(file);
                    }
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
                                    if(res.error === 0) window.location.href = "editarUsuario.php?id=" + res.id + "&id_aplicacion=" + res.id_aplicacion;
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
                            <input type="hidden" id="id" name="id" value="<?= $id ?>" />
                            <input type="hidden" id="id_aplicacion" name="id_aplicacion" value="<?= $id_aplicacion ?>" />
                            <input type="hidden" id="accion" name="accion" value="<?= ACCION ?>" />
                            <div class="row">
                                <div class="col-3 text-center">
                                    <img id="preview" src="imagenes/<?= $usuario["foto"]."?".round(microtime(true)*1000) ?>"  alt="Vista previa de imagen" class="img-7xx rounded-circle" style="max-height:200px;">
                                </div>
                                <div class="col-9 p-0">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label for="nombre" class="form-label">Nombre (s)</label>
                                            <input type="text" class="form-control" id="nombre" name="nombre" value="<?= $usuario["nombre"] ?>" required />
                                            <div class="valid-feedback">Correcto</div>
                                            <div class="invalid-feedback">Obligatorio</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="ap_paterno" class="form-label">A. Paterno</label>
                                            <input type="text" class="form-control" id="ap_paterno" name="ap_paterno" value="<?= $usuario["ap_paterno"] ?>" required />
                                            <div class="valid-feedback">Correcto</div>
                                            <div class="invalid-feedback">Obligatorio</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="ap_materno" class="form-label">A. Materno</label>
                                            <input type="text" class="form-control" id="ap_materno" name="ap_materno" value="<?= $usuario["ap_materno"] ?>" required />
                                            <div class="valid-feedback">Correcto</div>
                                            <div class="invalid-feedback">Obligatorio</div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-7">
                                            <label for="correo" class="form-label">Correo Electrónico</label>
                                            <input type="email" class="form-control" id="correo" name="correo" value="<?= $usuario["correo"] ?>" required />
                                            <div class="valid-feedback">Correcto</div>
                                            <div class="invalid-feedback">Obligatorio</div>
                                        </div>
                                        <div class="col-md-5">
                                            <label for="status" class="form-label">Estatus</label>
                                            <select class="form-select" id="status" name="status" required>
                                                <option selected disabled value="">Seleccione</option>
                                                <?php for($x=1;$x<=count($catStatus);$x++){ ?>
                                                    <option value="<?= $catStatus[$x]["id"] ?>"><?= $catStatus[$x]["nombre"] ?></option>
                                                <?php } ?>
                                            </select>
                                            <div class="valid-feedback">Correcto</div>
                                            <div class="invalid-feedback">Obligatorio</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row gx-3">
                                <div class="col-md-4">
                                    <label for="imagen" class="form-label">Imagen</label>
                                    <input type="file" class="form-control" id="imagen" name="imagen" value="<?= $usuario["imagen"] ?>" accept="image/*" />
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Obligatorio</div>
                                </div>
                                <?php if($id_aplicacion>0){ ?>
                                    <div class="col-md-4">
                                        <label for="rol" class="form-label">Rol</label>
                                        <select class="form-select" id="rol" name="rol" required>
                                            <option value="">Seleccione</option>
                                            <?php for($x=1;$x<=count($catRoles);$x++){ ?>
                                                <option value="<?= $catRoles[$x]["id"] ?>"><?= $catRoles[$x]["nombre"] ?></option>
                                            <?php } ?>
                                        </select>
                                        <div class="valid-feedback">Correcto</div>
                                        <div class="invalid-feedback">Obligatorio</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="area" class="form-label">Área</label>
                                        <select class="form-select" id="area" name="area" required>
                                            <option value="">Seleccione</option>
                                            <?php for($x=1;$x<=count($catAreas);$x++){ ?>
                                                <option value="<?= $catAreas[$x]["id"] ?>"><?= $catAreas[$x]["nombre"] ?></option>
                                            <?php } ?>
                                        </select>
                                        <div class="valid-feedback">Correcto</div>
                                        <div class="invalid-feedback">Obligatorio</div>
                                    </div>
                                <?php } ?>
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