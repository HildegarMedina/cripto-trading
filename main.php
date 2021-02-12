<?php 

    //Token
    $token = "YOUR_TOKEN";
    
    //Sitio web
    $website = "https://api.telegram.org/bot" . $token;

    //Obtener datos
    $data = file_get_contents("php://input");

    //Decodificar datos
    $data = json_decode($data, TRUE);

    //Obtener id del mensaje
    $chat_id = $data["message"]["chat"]["id"];

    //Obtener el tipo de chat
    $chat_type = $data["message"]["chat"]["type"];

    //Obtener mensaje
    $message = $data["message"]["text"];

    //Tomar solo el comando
    $command = explode(" ", $message);
    $command = $command[0];

    //Casos
    switch ($command) {

        //Si el usuario inicia el bot
        case '/start':

            //Obtener el id, nombre y apellido
            $user_id = $data["message"]["from"]["id"];
            
            //Vertificar si tiene el nombre
            if (isset($data["message"]["from"]["first_name"])) {
                $first_name = $data["message"]["from"]["first_name"];
            }else {
                $first_name = "empty";
            }

            //Verificar si tiene el apellido
            if (isset($data["message"]["from"]["last_name"])) {
                $last_name = $data["message"]["from"]["last_name"];
            }else {
                $last_name = "empty";
            }

            //Solicitar métodos a utilizar
            require_once("modules/account/user.php");

            //Crear usuario
            createUser($user_id, $first_name, $last_name);

            //Mostrar mensaje
            $response = "Welcome to our team, we are delighted to see you here. Invest now and earn money";
            $response = urlencode($response);
            sendMessage($chat_id, $response, $website);
            break;

        //Si el usuario pide ver los planes
        case '/plans':
            $response = "<b>PLAN BASIC</b>";
            $response .= "\n\n";
            $response .= "<b>3% daily for 20 days + principal return</b> \n";
            $response .= "<b>Total:</b> 160% return \n";
            $response .= "<b>Amount:</b> $1 - $100 \n\n";
            $response .= "Withdrawal in less than 24 hours";
            $response = urlencode($response);
            sendMessage($chat_id, $response, $website);
            break;

        //Si el usuario quiere calcular sus ganancias
        case '/calculator':

            //Separar datos
            $calculator = explode(" ", $message);

            //Verificar si pasaron los parámetros
            if(count($calculator) > 1) {

                //Verificar el valor
                $usd = $calculator[1];

                if (!is_numeric($usd)) {
                    $response = "You must enter a valid number. \n";
                }else {

                    //Solicitar métodos a utilizar
                    require_once("modules/utilities/calculator.php");

                    //Calcular
                    $response = profitCalculate($usd);
                   
                }
                
                $response = urlencode($response);
                sendMessage($chat_id, $response, $website);
                
            }else {
                $response = "<b>Calculator</b>";
                $response .= "\n\n";
                $response .= "To calculate benefits, use the following command: \n\n";
                $response .= "<b>/calculator {amount-usd}</b>\n";
                $response .= "<b>Example:</b> /calculator 10 \n\n";
                $response = urlencode($response);
                sendMessage($chat_id, $response, $website);
            }
            break;


        //Si el usuario quiere invertir
        case '/makedeposit':

            //Separar datos
            $makedeposit = explode(" ", $message);

            //Verificar si pasaron los parámetros
            if(count($makedeposit) > 1) {

                //Obtener el id, nombre y apellido
                $user_id = $data["message"]["from"]["id"];

                //Verificar el metodo
                $method = $makedeposit[1];

                if ($method != "payeer") {
                    $response = "Sorry, we don't work with that payment method.\n";
                }else {
                    //Verificar monto
                    $amount = $makedeposit[2];
    
                    //Si está dentro del rango, entonces procede
                    if ($amount >= 0.01 && $amount <= 100) {
    
                        //Solicitar métodos a utilizar
                        require_once("modules/account/deposit.php");
    
                        //Hacer deposito
                        $response = makeDeposit($user_id, $amount) ;
    
                    //Si se sale del rango
                    }else {
                        $response = "The amount must be between the values $1 - $100";
                    }
                }
                
                $response = urlencode($response);
                sendMessage($chat_id, $response, $website);
                
            }else {
                $response = "<b>Make Deposit</b>";
                $response .= "\n\n";
                $response .= "To make a deposit, use the following command: \n\n";
                $response .= "<b>/makedeposit payeer {amount-usd}</b>\n";
                $response .= "<b>Example:</b> /makedeposit payeer 10 \n\n";
                $response = urlencode($response);
                sendMessage($chat_id, $response, $website);
            }
            break;

        //Si el usuario quiere ver sus depositos
        case '/deposits':

            //Solicitar métodos a utilizar
            require_once("modules/account/deposit.php");

            //Obtener el id, nombre y apellido
            $user_id = $data["message"]["from"]["id"];

            //Hacer deposito
            $response = viewDeposits($user_id) ;

            $response = urlencode($response);
            sendMessage($chat_id, $response, $website);
            break;
            
        //Si el usuario pide el balance
        case '/balance':

            //Solicitar métodos a utilizar
            require_once("modules/account/user.php");

            //Obtener id del usuario
            $user_id = $data["message"]["from"]["id"];

            //Buscar el monto del balance
            $response = showBalance($user_id);
            $response = urlencode($response);
            sendMessage($chat_id, $response, $website);

            break;

        //Si el usuario pide retirar
        case '/withdrawal':
            
             //Separar datos
             $withdrawal = explode(" ", $message);

             //Verificar si pasaron los parámetros
             if(count($withdrawal) > 3) {
 
                //Verificar el valor
                $method = $withdrawal[1];
                $account = $withdrawal[2];
                $usd = $withdrawal[3];
 
                if ($method != "payeer") {
                    $response = "Sorry, we don't work with that payment method.\n";
                }else {
                    if (!is_numeric($usd)) {
                        $response = "You must enter a valid amount. \n";
                    }else {
    
                        //Obtener el id, nombre y apellido
                        $user_id = $data["message"]["from"]["id"];
     
                        //Solicitar métodos a utilizar
                        require_once("modules/payments/withdrawal.php");
     
                        //Calcular
                        $response = withdrawal($account, $usd, $user_id);
                        
                    }
                }
                 
                 $response = urlencode($response);
                 sendMessage($chat_id, $response, $website);
                 
            }else {
                $response = "<b>Withdrawal</b>";
                $response .= "\n\n";
                $response .= "You can perform the withdrawal using the following command: \n\n";
                $response .= "<b>/withdrawal payeer {account-id} {amount-usd}</b>\n";
                $response .= "<b>Example:</b> /withdrawal payeer P1029693457 35 \n\n";
                $response .= "Withdrawal in less than 24 hours";
                $response = urlencode($response);
                sendMessage($chat_id, $response, $website);
             }
             break;

        //Si el usuario pide ver la hora del server
        case '/datetime':
            $response = "<b>Datetime</b>";
            $response .= "\n\n";
            $date = date("Y-m.d H:i:s");
            $response .= "<b>" . $date . "</b> \n";
            $response = urlencode($response);
            sendMessage($chat_id, $response, $website);
            break;
        
        default:

            $response = "Sorry, our system has not recognized the command <b>$message</b>, be sure to write it correctly";
            $response = urlencode($response);
            sendMessage($chat_id, $response, $website);
            break;
    }

    //Mostrar planes
    function sendMessage ($id, $text, $website) {

        //Establecer url para enviar mensaje
        $url = "$website/sendMessage?chat_id=$id&text=$text&parse_mode=HTML";
        
        //Enviar petición
        file_get_contents($url);

    }

?>