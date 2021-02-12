<?php 

//Obtener datos de la bbbdd
require_once("modules/config/bbdd.php");

//Agregar retiro a la lista, para ser procesado manualmente
function withdrawal($account, $amount, $id_user) {

    //Crear la conexión
    $base = conection();

    //Verificar que el usuario tenga disponible ese dinero
    $sql = "SELECT balance, pending FROM users WHERE id = :id_user";

    //Preparar consulta
    $result = $base->prepare($sql);

    //Ejecutar consulta
    $result->execute(array(":id_user"=>$id_user));

    //Contar registros encontrados
    $count = $result->rowCount();
    
    //Verificar si se ingresó el registro
    if ($count > 0) {

        while ($row=$result->fetch(PDO::FETCH_ASSOC)) {
             $balance = $row["balance"];
             $pending = $row["pending"];
        }

        if ($balance >= $amount) {

            //Restamos el dienro al usuario
            $new_balance = $balance - $amount;
            $new_pending = $pending + $amount;
            
            //Actualizamos el balance del usuario y agregamos el dinero pendiente
            $sql_new_balance = "UPDATE `users` SET `balance`= :balance, `pending`= :pending WHERE id = :id_user";

            //Preparar consulta
            $result_new_balance = $base->prepare($sql_new_balance);

            //Ejecutar consulta
            $result_new_balance->execute(array(":balance"=>$new_balance, ":pending"=>$new_pending, ":id_user"=>$id_user));

            //Actualizamos el balance del usuario y agregamos el dinero pendiente
            $sql_withdrawal = "INSERT INTO `withdrawal`(`account`, `amount`, `date`, `id_user`) VALUES (:account, :amount , NOW(), :id_user)";

            //Preparar consulta
            $result_withdrawal = $base->prepare($sql_withdrawal);

            //Ejecutar consulta
            $result_withdrawal->execute(array(":account"=>$account, ":amount"=>$amount, ":id_user"=>$id_user));

            $count_withdrawal = $result_withdrawal->rowCount();

            if ($count_withdrawal > 0) {

                //Enviar email
                $msg = "The user with the id $id_user has requested a withdrawal in the amount of $amount for their Payeer account $account";
               
                mail("medinahildegar1@gmail.com", "New Withdrawal request - $id_user", $msg);

                return "<b>Your withdrawal request has been sent</b>\nIn less than 24 hours your order will be processed";
            }

        }else {
            return "You don't have enough funds";
        }
    
    }else {
        return "fatal error 3";
    }

}

?>