<?php
    //-- CONSTANTES --
    define("NIVEL","../");

    //-- CONECTA A LA BASE DE DATOS --
    require NIVEL."com/configBD.php";

    class Datos {
        public static function select_cat_aplicaciones($status) {
            global $dbAdmin;
            $params = array($status);
            $prepare = $dbAdmin->prepare("SELECT * FROM cat_aplicaciones WHERE status = ?");
            return $dbAdmin->execute($prepare,$params);
        }

        public static function find_cat_aplicaciones($id) {
            global $dbAdmin;
            $params = array($id);
            $prepare = $dbAdmin->prepare("SELECT * FROM cat_aplicaciones WHERE id = ?");
            return $dbAdmin->execute($prepare,$params);
        }

        public static function create_cat_aplicaciones($data) {
            global $dbAdmin;
            $dbAdmin->autoExecute("cat_aplicaciones",$data,"INSERT");
            return $dbAdmin->insert_Id();
        }

        public static function update_cat_aplicaciones($data) {
            global $dbAdmin;
            $condition = "id = ".$data["id"];
            $dbAdmin->autoExecute("cat_aplicaciones",$data,"UPDATE",$condition);
        }
    }
?>