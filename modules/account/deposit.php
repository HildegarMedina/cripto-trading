<?php 

    //Obtener datos de la bbbdd
    require_once("modules/config/bbdd.php");

    //Agregar deposito en la lista para su posterior pago
    function makeDeposit($id, $amount) {

        //Crear la conexión
        $base = conection();

        //Crear token
        $token = bin2hex(openssl_random_pseudo_bytes(16));

        //Insertar el deposito
        $sql = "INSERT INTO `deposits_history`(`token`, `amount`, `date`, `id_user`) VALUES (:token, :amount, NOW(), :id_user)";

        //Preparar consulta
        $result = $base->prepare($sql);

        //Ejecutar consulta
        $result->execute(array(":token"=>$token, ":amount"=>$amount, ":id_user"=>$id));

        //Contar registros insertados
        $count = $result->rowCount();
        
        //Verificar si se ingresó el registro
        if ($count > 0) {

            return "Done, you can pay at the following link <a href='https://hyipdailymonitor.com/pay.php?token=$token'>https://hyipdailymonitor.com/pay.php?token=$token</a>";
        
        }else {
            return "fatal error 2";
        }

    }

    //Mostrar lista de depositos
    function viewDeposits($id) {

        //Crear la conexión
        $base = conection();

        //Crear token
        $token = bin2hex(openssl_random_pseudo_bytes(16));

        //Insertar el deposito
        $sql = "SELECT * FROM `deposits` WHERE id_user = :id_user AND status = 'Running'";

        //Preparar consulta
        $result = $base->prepare($sql);

        //Ejecutar consulta
        $result->execute(array(":id_user"=>$id));

        //Contar registros insertados
        $count = $result->rowCount();
        
        //Verificar si se ingresó el registro
        if ($count > 0) {

            $list = "<b>Deposits:</b>\n\n";
            $list .= "=============================================\n";

            while ($row=$result->fetch(PDO::FETCH_ASSOC)) {
            
                //Obtener datos
                $plan = $row["plan"];
                $amount = $row["amount"];
                $start = $row["start"];

                //Sumar 20 días a la fecha de inicio
                $end = date("Y-m-d G:i:s", strtotime($start."+ 20 days")); 

                //Formatear fecha
                $start = date("M, d Y", strtotime($start));

                //Expira
                $rem = strtotime($end) - time();
                $day = floor($rem / 86400);
                $hr  = floor(($rem % 86400) / 3600);
                $min = floor(($rem % 3600) / 60);
                
                $list .= "<b>Plan:</b> $plan \n";
                $list .= "<b>Date:</b> $start \n";
                $list .= "<b>Amount:</b> $amount \n";
                $list .= "<b>Expire in $day days $hr hours $min minutes (calendar days)</b>\n";
                $list .= "=============================================\n";

                
            }
            
            return $list;
            
        }else {
            return "<b>Deposits:</b>\n\nYou have no active deposits";
        }

    }

?>