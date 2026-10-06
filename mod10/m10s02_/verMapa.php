<?php
    //-- CONSTANTES --
    define("NIVEL","../../");

    //-- VERIFICA SI EXISTE SESION --
    session_start();
    if(empty($_SESSION["id_usuario"])){
        session_destroy();
        echo "<script>window.parent.cerrarRecargar();</script>";
    }

    //-- PARAMETROS Y VARIABLES --
    $id_sistema = $_GET["id"];

    //-- CONECTA A LA BASE DE DATOS --
    include(NIVEL."com/configBD.php");

    //-- OBTIENE INFORMACION DEL CONTRATO --
    $sql = "SELECT nombre, latitud, longitud FROM cat_sistemas WHERE id = $id_sistema";
    $stmt = $conexion -> query($sql);
    $row = $stmt->fetch_row();
    $sistema = $row[0];
    $latitud = $row[1];
    $longitud = $row[2];
    $zoom = "17";



    //-- COORDENADAS DEL DUCTO SISTEMA--
    $cont = 1;
    $stmt = $conexion -> query("SELECT * FROM tbl_mapa WHERE sistema = $id_sistema AND tipo = 'linea' AND descripcion = 'Ducto'");
    while($row = $stmt->fetch_assoc()){
        $coordL1[$cont]["lat"] = $row["latitud"];
        $coordL1[$cont]["lon"] = $row["longitud"];
        $cont++;
    }


    //-- CIERRA CONEXION A LA BASE DE DATOS --
    $conexion -> close();
?>


<!DOCTYPE html>
<html lang="es">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Contrato</title>

        <!-- CSS -->
        <link rel="stylesheet" href="<?php echo NIVEL; ?>vendor/bootstrap-4.5.3/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?php echo NIVEL; ?>vendor/fontawesome-free-5.15.1/css/all.min.css">
		<link rel="stylesheet" href="<?php echo NIVEL; ?>css/estilos.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto+Condensed">
        <style type="text/css">
            html { overflow-x: hidden; }
            body { font-family: 'Roboto Condensed', sans-serif; }
            #map {
                height: 550px;
                width: 100%;
                overflow: hidden;
                float: left;
                border: thin solid #333;
            }
        </style>

        <!-- JAVASCRIPT -->
        <script src="<?php echo NIVEL; ?>vendor/jquery-3.5.1/jquery-3.5.1.min.js"></script>
        <script src="<?php echo NIVEL; ?>vendor/bootstrap-4.5.3/js/bootstrap.bundle.min.js"></script>
        <script src="<?php echo NIVEL; ?>vendor/validate/jquery.validate.js"></script>
        <script type="text/javascript">
            $(document).ready(function(){
                window.parent.tamano(650);

                $("#guardar").click(function(){ $(this).attr("disabled","disabled"); $("#formulario").submit(); });
                $("#cerrar").click(function(){ window.parent.cerrar(); });

                //-- VALIDACIONES --
                jQuery.validator.addMethod("notEqual", function(value,element,param) { return this.optional(element) || value!=param;}, "Seleccione un valor");
                $("#formulario").validate({
                    event: "blur",
                    rules: { },
                    messages: { },
                    debug: true,
                    errorElement: "label",
                    submitHandler: function(form){
                        var campos = new FormData(form);
                        $.ajax({
                            type: 'POST',
                            url: 'guardarMapa.php',
                            contentType: false,
                            data: campos,
                            processData:false,
                            success: function(msg){
                                if(msg.length>0) alert(msg);
                                else window.parent.cerrarRecargar();
                            }
                        });
                    },
                    invalidHandler: function(){
                        $("#guardar").removeAttr("disabled");
                    }
                });

            });
        </script>

    </head>

    <body>
        <ul class="list-group">
            <li class="list-group-item list-group-item-info pb-1">
                <h6><i class="fas fa-map-marked-alt"></i> Mapa del terreno</h6>
            </li>
        </ul>
        <div id="map"></div>
        <form id="formulario">
            <div class="row pt-1">
                <div class="col-12 text-center pt-2 pb-0">
                    <input type="hidden" name="id_contrato" id="id_contrato" value="<?php echo $id_contrato;?>">
                    <input type="hidden" name="id_sistema" id="id_sistema" value="<?php echo $id_sistema;?>">
                    <input type="hidden" name="coordenadas" id="coordenadas" value="<?php echo $coordenadas;?>">
                    <button type="submit" class="btn btn-sm btn-outline-info" id="guardar">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="cerrar">
                        <i class="fas fa-times"></i> Cerrar ventana
                    </button>
                </div>
            </div>
        </form>

        <script>
            var map;
            function initMap() {
                map = new google.maps.Map(document.getElementById('map'), {
                    center: new google.maps.LatLng(<?php echo $latitud.",".$longitud; ?>),
                    zoom: <?php echo $zoom;?>,
                    mapTypeId: 'satellite'
                });

                var ducto1Coords = [
                    <?php if(count($coordL1)>0) {?>
                        <?php for($x=1; $x<=count($coordL1); $x++){ ?>
                            new google.maps.LatLng(<?php echo $coordL1[$x]["lat"].",".$coordL1[$x]["lon"]; ?>),
                        <?php } ?>
                    <?php } else { ?>
                        new google.maps.LatLng(<?php echo $latitud.",".$longitud; ?>),
                        new google.maps.LatLng(<?php echo $latitud.",".($longitud+0.0005); ?>),
                    <?php } ?>
                ];

                linea1 = new google.maps.Polyline({
                    path: ducto1Coords,
                    draggable: true, // turn off if it gets annoying
                    editable: true,
                    geodesic: true,
                    strokeColor: "#000C66",
                    strokeOpacity: 1.0,
                    strokeWeight: 5,
                });

                linea1.setMap(map);



                var htmls = "";
                for (var i = 0; i < linea1.getPath().getLength(); i++) { htmls += linea1.getPath().getAt(i).toUrlValue(5)+"|"; }
                $("#coordenadas").val(htmls);
                console.log(htmls);

                google.maps.event.addListener(linea1.getPath(), "insert_at", getPolygonCoords);
                google.maps.event.addListener(linea1.getPath(), "set_at", getPolygonCoords);

                linea1.addListener("click", () => { alert("Ducto Pemex"); });


            }


            function getPolygonCoords() {
                var htmlStr = "";
                for (var i = 0; i < linea1.getPath().getLength(); i++) { htmlStr += linea1.getPath().getAt(i).toUrlValue(5)+"|"; }
                console.log(htmlStr);
                $("#coordenadas").val(htmlStr);
            }

        </script>
        <script async
            src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAjcj7rVTuh2tRj_9dm3wQNhKEWlr4jfzU&callback=initMap">
        </script>
    </body>
</html>
