<?php 

    //Obtener datos de la bbbdd
    require_once("../config/bbdd.php");

    //Crear la conexión
    $base = conection();

    //Buscar depositos en marcha
    $sql = "SELECT * FROM `deposits` WHERE status = 'Running'";

    //Preparar consulta
    $result = $base->prepare($sql);

    //Ejecutar consulta
    $result->execute(array());

    //Contar registros insertados
    $count = $result->rowCount();
    
    //Verificar si se encontraron registro
    if ($count > 0) {

        while ($row=$result->fetch(PDO::FETCH_ASSOC)) {
        
            //Obtener datos
            $id = $row["id"];
            $amount = $row["amount"];
            $start = $row["start"];
            $id_user = $row["id_user"];

            //Sumar 20 días a la fecha de inicio
            $end = date("Y-m-d H:i:s", strtotime($start."+ 20 days")); 

            //transformar a formato unix
            $end = strtotime(date($end,time()));
        
            //Tomar fecha de hoy
            $today = strtotime(date("Y-m-d H:i:s",time()));
            
            //Si ya pasamos la fecha final
            if($today >= $end) {

                //Actualizamos el deposito como pagado
                $sql_check = "UPDATE `deposits` SET `status`= 'Finished' WHERE id = :id";

                //Preparar consulta
                $result_check = $base->prepare($sql_check);

                //Ejecutar consulta
                $result_check->execute(array(":id"=>$id));

                //Contar registros modificados
                $count_check = $result_check->rowCount();

                //Si se marcó el deposito como pagado
                if ($count_check > 0) {

                    //Obtener el balance del usuario
                    $sql_balance = "SELECT balance FROM `users` WHERE id = :id_user";

                    //Preparar consulta
                    $result_balance = $base->prepare($sql_balance);

                    //Ejecutar consulta
                    $result_balance->execute(array(":id_user"=>$id_user));

                    //Gurdar el balance
                    while ($row_balance=$result_balance->fetch(PDO::FETCH_ASSOC)) {
                        $balance = $row_balance["balance"];
                    }

                    //Devolver el 100% + 3%
                    $last_profit = $amount * 3 / 100;
                    $balance = $balance + $amount + $last_profit;

                    //Actualizamos el deposito
                    $sql_new_balance = "UPDATE `users` SET `balance`= :balance WHERE id = :id_user";

                    //Preparar consulta
                    $result_new_balance = $base->prepare($sql_new_balance);

                    //Ejecutar consulta
                    $result_new_balance->execute(array(":balance"=>$balance, ":id_user"=>$id_user));

                }

            }else {
                
                //Tomamos la fecha que inició y lo convertimos a unix
                $startref = strtotime($start);
                $starttime = strtotime($start);
                $today = strtotime(date("Y-m-d H:i:s"));

                # 24 horas * 60 minutos por hora * 60 segundos por minuto
                $dia = 86400;

                while($starttime <= $today) {

                    $tempdate = date("Y-m-d H:i:s", $starttime);
                    
                    //Si la fecha es distinta a la de inicio
                    if (strtotime($tempdate) != $startref) {

                        //Establecer ganancia del día
                        $profit = $amount * 3 / 100;

                        //Verificar que ya se haya realizado el pago
                        $sql_verify = "SELECT * FROM `payments` WHERE id_deposit = :id_deposit AND date LIKE '%$tempdate%'";

                        //Preparar consulta
                        $result_verify = $base->prepare($sql_verify);

                        //Ejecutar consulta
                        $result_verify->execute(array(":id_deposit"=>$id));

                        //Contar
                        $count_verify = $result_verify->rowCount();

                        //Si no existe el pago, hazlo
                        if ($count_verify == 0) {

                            //Hacer pago
                            $sql_pay = "INSERT INTO `payments`(`amount`, `date`, `id_deposit`) VALUES (:amount, :date, :id_deposit)";

                            //Preparar consulta
                            $result_pay = $base->prepare($sql_pay);

                            //Ejecutar consulta
                            $result_pay->execute(array(":amount"=>$profit, ":date"=>$tempdate, ":id_deposit"=>$id));

                            //Contar
                            $count_pay = $result_pay->rowCount();

                            //Si se registró, entonces añadele el saldo al usuario
                            if ($count_pay != 0) {
                                
                                //Obtener el balance del usuario
                                $sql_balance = "SELECT balance FROM `users` WHERE id = :id_user";

                                //Preparar consulta
                                $result_balance = $base->prepare($sql_balance);

                                //Ejecutar consulta
                                $result_balance->execute(array(":id_user"=>$id_user));

                                //Gurdar el balance
                                while ($row_balance=$result_balance->fetch(PDO::FETCH_ASSOC)) {
                                    $balance = $row_balance["balance"];
                                }

                                //Actualizar su balance
                                $balance = $balance + $profit;

                                //Actualizamos el deposito
                                $sql_new_balance = "UPDATE `users` SET `balance`= :balance WHERE id = :id_user";

                                //Preparar consulta
                                $result_new_balance = $base->prepare($sql_new_balance);

                                //Ejecutar consulta
                                $result_new_balance->execute(array(":balance"=>$balance, ":id_user"=>$id_user));

                                echo "Pago realizado a $id_user por el monto de $profit | $tempdate <br>";

                            }
                            
                        }else {
                            echo "El pago ya fué realizado con anterioridad | $id_user | $tempdate <br>";
                        }

                    }


                    # Sumar el incremento para que en algún momento termine el ciclo
                    $starttime += $dia;
                }
                
            }

        }
        
    }else {
        echo "No se encontraron registros";
    }

?>