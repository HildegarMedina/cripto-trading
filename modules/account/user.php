<?php 

    //Obtener datos de la bbbdd
    require_once("modules/config/bbdd.php");

    //Crear usuario nuevo en la base de datos
    function createUser($id, $name, $lastname) {

        //Crear la conexión
        $base = conection();

        //Buscar el usuario
        $sql_search = "SELECT * FROM `users` WHERE id = :id";

        //Preparar consulta
        $result_search = $base->prepare($sql_search);

        //Ejecutar consulta
        $result_search->execute(array(":id"=>$id));

        //Contar usuarios encontrados
        $count = $result_search->rowCount();

        //Ingresa al usuario solo si no existe
        if($count == 0) {

            //Insertar el usuario
            $sql = "INSERT INTO `users`(`id`, `name`, `lastname`, `date`) VALUES (:id, :name, :lastname, NOW())";
    
            //Preparar consulta
            $result = $base->prepare($sql);
    
            //Ejecutar consulta
            $result->execute(array(":id"=>$id, ":name"=>$name, ":lastname"=>$lastname));

        }


    }

    //Buscar dato del usuario la base de datos
    function showBalance($id) {

        //Crear la conexión
        $base = conection();

        //Buscar el usuario
        $sql = "SELECT balance, pending FROM `users` WHERE id = :id";

        //Preparar consulta busqueda
        $result = $base->prepare($sql);

        //Ejecutar consulta busqueda
        $result->execute(array(":id"=>$id));

        //Contar registros encontrados
        $count = $result->rowCount();
        
        //Verificar si existe el usuario
        if ($count > 0) {

            while ($row=$result->fetch(PDO::FETCH_ASSOC)) {
                $balance = $row["balance"];
                $pending = $row["pending"];
            }

            $response = "<b>Your balance:</b> " . $balance . " USD";
            if ($pending > 0) {
                $response .= "\n\nPending withdrawal: $pending USD";
            }

            return $response;
        
        }else {
            return "fatal error 1";
        }

    }

    //Buscar depositos
    // function showDeposits($id) {

    //     //Crear la conexión
    //     $base = conection();

    //     //Buscar el usuario
    //     $sql = "SELECT * FROM `deposits` WHERE id_user = :id";

    //     //Preparar consulta busqueda
    //     $result = $base->prepare($sql);

    //     //Ejecutar consulta busqueda
    //     $result->execute(array(":id"=>$id));

    //     //Contar registros encontrados
    //     $count = $result->rowCount();
        
    //     //Verificar si existe el usuario
    //     if ($count > 0) {

    //         while ($row=$result->fetch(PDO::FETCH_ASSOC)) {
    //             $balance = $row["balance"];
    //         }

    //         return "";
        
    //     }else {
    //         return "You still have no deposits";
    //     }

    // }

?>