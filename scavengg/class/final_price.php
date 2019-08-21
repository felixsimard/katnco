<?php
// Add taxes and service fee to brut price
function finalPrice($brut) {

    $taxes = $brut * 0.14975;

    $price_with_taxes = $brut + $taxes;

    //$service_fee = $price_with_taxes * 0.10;

    //$final_price = round(($service_fee + $price_with_taxes) / 10) * 10; // round to nearest 10
    $final_price = round(($price_with_taxes) / 10) * 10; // round to nearest 10

    return $final_price;

}

?>