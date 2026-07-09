<?php
    //-- MODELO --
    require "models/modelo.php";

    class Controlador {

        //----------------------
        //-- METODO PRINCIPAL --
        //----------------------
        public function index() {
            $reg = array();
            $cont = 1;

            $status = ($_SERVER["REQUEST_METHOD"]=="POST")? $_POST["status"] : 1 ;
            $result = Datos::select_cat_aplicaciones($status);
            while(!$result->EOF){
                $reg[$cont]["id"] = $result->fields["id"];
                $reg[$cont]["nombre"] = $result->fields["nombre"];
                $reg[$cont]["descripcion"] = $result->fields["descripcion"];
                if($result->fields["status"]==0){ $reg[$cont]["estatus"] = "Inactivo"; }
                if($result->fields["status"]==1){ $reg[$cont]["estatus"] = "Activo"; }
                $result->MoveNext();
                $cont++;
            }

            require "views/principal.php";
        }

        //----------------------------
        //-- METODO EDITAR REGISTRO --
        //----------------------------
        public function editar($id){
            $reg = array();

            $result = Datos::find_cat_aplicaciones($id);
            $reg["id"] = $result->fields["id"];
            $reg["nombre"] = $result->fields["nombre"];
            $reg["descripcion"] = $result->fields["descripcion"];
            $reg["servidor"] = $result->fields["servidor"];
            $reg["tipo_conexion"] = $result->fields["tipo_conexion"];
            $reg["usuario_bd"] = $result->fields["usuario_bd"];
            $reg["password_bd"] = $result->fields["password_bd"];
            $reg["bd"] = $result->fields["bd"];
            $reg["historial"] = $result->fields["historial"];
            $reg["link"] = $result->fields["link"];
            $reg["minutos_sesion"] = $result->fields["minutos_sesion"];
            $reg["status"] = $result->fields["status"];
            
            require "views/editarRegistro.php";
        }

        //-----------------------------
        //-- METODO GUARDAR REGISTRO --
        //-----------------------------
        public function guardar($datos){
            $info = array();
            $id = 0;

            if($datos["id"]==0){
                $id = Datos::create_cat_aplicaciones($datos);
            } 
            if($datos["id"]>0){
                Datos::update_cat_aplicaciones($datos);
                $id = $datos["id"];
            }

            $info["id"] = $id;
            $info["error"] = 0;
            $info["titulo"] = "¡Informacion guardada exitosamente!";
            $info["mensaje"] = "Se creo el registro";
            echo json_encode($info);
        }

    }
?>
