<?php

    //-- CONSTANTES --
    if (!defined('NIVEL')) define('NIVEL', '../../');
    require NIVEL.'../vendor/autoload.php';


use Smalot\PdfParser\Parser;

$parser = new Parser();
$pdf = $parser->parseFile('PO 1662 GNP ARITHMO MPS.pdf');

$texto = $pdf->getText();
echo $texto;
