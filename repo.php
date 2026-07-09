<?php
    define("NIVEL","../");


    //-- CONECTA A LA BASE DE DATOS --
    include(NIVEL."../com/configBD_SCADA.php");


    // Depuración: mostrar errores de PHP mientras se arregla la inclusión
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Validar que la conexión se creó
    if (!isset($dbSCADA) || !is_object($dbSCADA)) {
        die("Error: no se cargó la conexión \$dbSCADA. Verifica la ruta del archivo com/configBD_SCADA.php y que no haya errores en él.");
    }

    $sql = "SELECT DISTINCT tbl1.emr, tbl1.id_erm, tbl1.tipo_erm AS Clasificacion_Sistema, tbl1.tup, tbl1.id_inter, ISNULL(tv.Volumen_MMBTU, 0) AS Volumen_MMBTU_Total
            FROM
    (
        SELECT
            id_inter,
            id_erm,
            tipo_erm,
            emr,
            tup
        FROM clientes_emr
        WHERE
            transmision != ''
            AND id_inter != '11046-01'
    ) tbl1
LEFT JOIN
    (
        SELECT
            id_erm,
            SUM(volumen_gjuoles / 1.055056) AS Volumen_MMBTU
        FROM
            tbl_clientes_volumenes
        WHERE
            fecha_hora = '2024-10-01'
        GROUP BY
            id_erm
    ) tv ON tv.id_erm = tbl1.id_erm
ORDER BY
    tbl1.id_inter,
    tbl1.tipo_erm;";


    $result = $dbSCADA->execute($sql);
    while(!$result->EOF){
         $catStatus[$cont]["emr"] = $result->fields["emr"];
         $catStatus[$cont]["id_erm"] = $result->fields["id_erm"];
         $catStatus[$cont]["Clasificacion_Sistema"] = $result->fields["Clasificacion_Sistema"];
         $catStatus[$cont]["tup"] = $result->fields["tup"];
         $catStatus[$cont]["id_inter"] = $result->fields["id_inter"];
       echo  $catStatus[$cont]["Volumen_MMBTU_Total"] = $result->fields["Volumen_MMBTU_Total"];
        $result->MoveNext();
    }
?>