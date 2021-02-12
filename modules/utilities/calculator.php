<?php 

    //Calcular ganancia
    function profitCalculate($amount) {

        $profit_day = $amount * 3 / 100;
        $total_return = $amount * 160 / 100;
        $profit_total = $profit_day * 20;

        $response = "<b>Calculator</b>\n\n";
        $response .= "<b>PLAN BASIC (20 days)</b>";
        $response .= "\n\n";
        $response .= "<b>Investment:</b> $amount USD\n";
        $response .= "<b>Profit by day:</b> $profit_day USD\n";
        $response .= "<b>Profit total:</b> $profit_total USD \n";
        $response .= "<b>Principal return:</b> 100% ($amount USD) \n\n";
        $response .= "<b>Total return:</b> $total_return USD\n\n";
        
        return $response;

    }

?>