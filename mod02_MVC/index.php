<?php
    require_once "controllers/controlador.php";
    $control = new Controlador();

    $action = $_GET["action"];
    $id = $_GET["id"];

    switch ($action) {
        case "editar":
            $control->editar($id);
            break;
        case "guardar":
            $control->guardar($_POST);
            break;
        default:
            $control->index();
            break;
    }
?>