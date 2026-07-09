<?php
    //-- CONSTANTES --
    define("NIVEL","../../");
    define("ACCION","EDITAR_SUBMENU");
    define("APLICACION","100");

    //-- PARAMETROS Y VARIABLES --
    $id = $_GET["id"];
    $id_aplicacion = $_GET["id_aplicacion"];
    $menu_padre = array();

    //-- CONECTA A LA BASE DE DATOS --
    include(NIVEL."../com/config.php");
    $dbAdmin = conectaBD("mysql","admin");

    //-- OBTIENE INFORMACION DEL MENU --
    if($id>0){
        $prepare = $dbAdmin->prepare("SELECT * FROM tbl_menu WHERE id = ?");
        $result = $dbAdmin->execute($prepare,[$id]);
        $reg["id"] = $result->fields["id"];
        $reg["nombre"] = $result->fields["nombre"];
        $reg["identificador"] = $result->fields["identificador"];
        $reg["directorio"] = $result->fields["directorio"];
        $reg["icono"] = htmlspecialchars($result->fields["icono"]);
        $reg["orden"] = $result->fields["orden"];
        $reg["padre"] = $result->fields["padre"];
        $reg["status"] = $result->fields["status"];
        $id_aplicacion = $result->fields["sistema"];
    }
    else {
        //-- OBTIENE ORDEN PARA NUEVO REGISTRO --
        $prepare = $dbAdmin->prepare("SELECT MAX(orden) FROM tbl_menu WHERE sistema = ? AND tipo = ?");
        $result = $dbAdmin->execute($prepare, [$id_aplicacion, 1]);
        $reg["orden"] = ($result->fields[0]>0) ? $result->fields[0] + 1 : "" ;

        //-- OBTIENE ID PARA NUEVO REGISTRO --
        $prepare = $dbAdmin->prepare("SELECT MAX(id) FROM tbl_menu WHERE sistema = ?");
        $result = $dbAdmin->execute($prepare, [$id_aplicacion]);
        $reg["id"] = ($result->fields[0]>0) ? $result->fields[0] + 1 : "" ;
    }

    //-- OBTIENE MENU PADRE --
    $cont = 1;
    $condicion = ($id>0) ? "" : " AND status = ?" ;
    $sql = "SELECT id, nombre
            FROM tbl_menu
            WHERE sistema = ?
            AND tipo = ?
            $condicion
            ORDER BY orden ASC";
    $prepare = $dbAdmin->prepare($sql);
    $params = [$id_aplicacion, 1];
    if($id==0) $params[] = 1;
    $result = $dbAdmin->execute($prepare, $params);
    while(!$result->EOF){
        $menu_padre[$cont]["id"] = $result->fields["id"];
        $menu_padre[$cont]["nombre"] = $result->fields["nombre"];
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
                window.parent.tamano(300,"modalSubmenu");

                <?php if($id>0){ ?>
                    $("#menu_padre option:selected").removeAttr("selected");
                    $("#menu_padre option[value='<?= $reg["padre"] ?>']").attr('selected', 'selected');
                    $("#status option:selected").removeAttr("selected");
                    $("#status option[value='<?= $reg["status"]; ?>']").attr('selected', 'selected');
                <?php } ?>

                $("#menu_padre").change(function(){
                    $("#padre").val($(this).val());
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
                                    res = JSON.parse(res);
                                    console.log(res);
                                    window.parent.parent.mensaje(res);
                                    if(res.error === 0) window.location.href = "editarSubmenu.php?id=" + res.id;
                                }
                                catch (e) {
                                    console.error("Error al parsear JSON:", e);
                                    window.parent.parent.mensaje({error: 1, titulo: "Error", mensaje: "No fue posible procesar la respuesta del servidor.", aceptar: 1});
                                }
                            },
                            error: function( xhr, err ) {
                                $(window.parent.document.body).loadingModal("destroy");
                                window.parent.parent.mensaje({error: 1, titulo: "Error", mensaje: "No fue posible establecer comunicación con el servidor.", aceptar: 1});
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
                            <input type="hidden" id="id_aplicacion" name="id_aplicacion" value="<?= $id_aplicacion ?>" />
                            <input type="hidden" id="accion" name="accion" value="<?= ACCION ?>" />

                            <div class="row gx-3">
                                <div class="col-md-9">
                                    <label for="menu_padre" class="form-label">Menu Padre</label>
                                    <select class="form-select" id="menu_padre" name="menu_padre" required>
                                        <option selected disabled value="">Seleccione</option>
                                        <?php for($x=1; $x<=count($menu_padre); $x++) { ?>
                                            <option value="<?= $menu_padre[$x]["id"] ?>"><?= $menu_padre[$x]["nombre"] ?></option>
                                        <?php } ?>
                                    </select>
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Obligatorio</div>
                                </div>
                                <div class="col-md-3">
                                    <label for="padre" class="form-label">Padre</label>
                                    <input type="number" class="form-control" id="padre" name="padre" value="<?= $reg["padre"] ?>" required readonly />
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Obligatorio</div>
                                </div>
                                <div class="col-md-2">
                                    <label for="id" class="form-label">Id</label>
                                    <input type="number" class="form-control" id="id" name="id" value="<?= $reg["id"] ?>" required <?= ($id>0)? "readonly" : ""; ?> />
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Obligatorio</div>
                                </div>
                                <div class="col-md-3">
                                    <label for="nombre" class="form-label">Nombre (s)</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?= $reg["nombre"] ?>" required />
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Obligatorio</div>
                                </div>
                                <div class="col-md-3">
                                    <label for="identificador" class="form-label">Identificador</label>
                                    <input type="text" class="form-control" id="identificador" name="identificador" value="<?= $reg["identificador"] ?>" required />
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Obligatorio</div>
                                </div>
                                <div class="col-md-4">
                                    <label for="directorio" class="form-label">Directorio</label>
                                    <input type="text" class="form-control" id="directorio" name="directorio" value="<?= $reg["directorio"] ?>" required />
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Obligatorio</div>
                                </div>
                                <div class="col-md-7">
                                    <label for="icono" class="form-label">Icono</label>
                                    <input type="text" class="form-control" id="icono" name="icono" value="<?= $reg["icono"] ?>" required />
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Obligatorio</div>
                                </div>
                                <div class="col-md-2">
                                    <label for="orden" class="form-label">Orden</label>
                                    <input type="number" class="form-control" id="orden" name="orden" value="<?= $reg["orden"] ?>" step="0.01" required />
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
                            <div class="col-12 text-center pt-2 pb-2">
                                <div class="btn btn-primary" id="enviar" >
                                    <i class="bi bi-floppy2"></i> Guardar
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
</html>