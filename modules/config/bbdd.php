<?php

    $db_name = "crypto_trading_db";
    $db_host = "localhost";
    $db_user = "criptobot";
    $db_pass = "lqZ[_Hj(o*L4";

    //Crear conexión
    function conection() {

        global $db_host, $db_name, $db_user, $db_pass;

        //Creación de la base de conexión
        $base = new PDO("mysql:host=$db_host; dbname=$db_name; charset=utf8", "$db_user", "$db_pass");
    
        //Establecer atributos
        $base->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //Retornar la conexión
        return $base;

    }

?>