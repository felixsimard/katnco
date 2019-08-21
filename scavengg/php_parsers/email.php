<?php 
include_once("../php_includes/db_conx.php");
?>
<?php

// ADMIN NOTIFICATIONS
function notify($to, $msg) {

    $content = $msg;
				 
	$from = "Scavengg <scavengg@gmail.com>";
	$subject = 'Scavengg Notification';

    $headers = "From: $from\r\n". 
            "MIME-Version: 1.0" . "\r\n" . 
            "Content-type: text/html; charset=UTF-8" . "\r\n";

	mail($to, $subject, $content, $headers); 
}

// WELCOME EMAIL
function welcome($to) {

    $welcome .= '</div>';
    $welcome .= '<div class="disclaimer">';
    $welcome .= '&copy;Scavengg, All Rights Reserved, '.date("Y").'<br>Montreal, Qc, Canada';
    $welcome .= '</div>';
    $welcome .= '</body>';
    //$welcome .= '</html>';

    $content = file_get_contents("../emails/welcome.php");
    $content .= $welcome;
				 
	$from = "Scavengg <scavengg@gmail.com>";
	$subject = 'Welcome to Scavengg';

    $headers = "From: $from\r\n". 
            "MIME-Version: 1.0" . "\r\n" . 
            "Content-type: text/html; charset=UTF-8" . "\r\n";

	mail($to, $subject, $content, $headers); 

}

function ordersummary($to, $cart_rows, $amount, $order_details, $promotion, $db_tran_id) {

    $order = '';

    $amount_before_taxes = $amount / 1.14975;

    $tps = round($amount_before_taxes * 0.05, 2);
    $tvq = round($amount_before_taxes * 0.09975, 2);
    
    $amount_before_taxes = $amount - ($tps + $tvq);
    $subtotal_without_promo = $amount_before_taxes + $promotion;

    $order .= ''.$order_details.'';
    $order .= '<div class="price_details"><div class="left">Subtotal</div> <div class="right">'.$subtotal_without_promo.'$</div></div>';
    if($promotion) {
        $order .= '<div class="price_details"><div class="left">Promo</div> <div class="right" style="color:#ff5050;">-'.$promotion.'$</div></div><div class="price_details"><div class="left">Subtotal</div> <div class="right">'.$amount_before_taxes.'$</div></div>';
    }
    $order .= '<div class="price_details"><div class="left">TPS (5%)</div> <div class="right">'.$tps.'$</div></div>';
    $order .= '<div class="price_details"><div class="left">TVQ (9.975%)</div> <div class="right">'.$tvq.'$</div></div>';
    $order .= '<div class="price_details" style="font-weight:800;"><div class="left">Total</div> <div class="right">CA$'.$amount.'</div></div>';
    $order .= '<hr><div class="fat_text">Thank you for choosing Scavengg.</div>';
    //$order .= '<a href="https://scavengg.com/"><button class="btns">Scavengg</button></a>';
    $order .= '</div>';
    $order .= '<div class="disclaimer">';
    $order .= '&copy;Scavengg, All Rights Reserved, '.date("Y").'<br>Montreal, Qc, Canada';
    $order .= '</div>';
    $order .= '</body>';
    //$order .= '</html>';

    $content = file_get_contents("../emails/ordersummary.php");
    $content .= $order; // add the order
				 
	$from = "Scavengg <scavengg@gmail.com>";
	$subject = 'Scavengg Order Confirmation - '.date("F d Y H:ia").' #'.$db_tran_id.'';

    $headers = "From: $from\r\n". 
            "MIME-Version: 1.0" . "\r\n" . 
            "Content-type: text/html; charset=UTF-8" . "\r\n";

	mail($to, $subject, $content, $headers); 

    // Send emails to admin
    mail("felixsimard@gmail.com", $subject, $content, $headers); 
    mail("lasrywinc@yahoo.com", $subject, $content, $headers); 

}

?>