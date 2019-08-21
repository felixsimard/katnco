<?php 
include_once("../php_includes/db_conx.php");
include_once("../php_includes/session.php");
include_once("../class/time_ago_class.php");
include_once("../class/phone_format.php");
include_once("../class/number_format.php");
include_once("email.php");
?>
<?php
// CHECK IF PROMO CODE VALID
if(isset($_POST["action"]) && $_POST["action"] == "check_promo_code") {

    $promo = preg_replace('#[^a-z0-9-]#i', '', $_POST['promo']); 

    $query = mysqli_query($db_conx, "SELECT value, calls FROM promotions WHERE promo='$promo' AND active='1' LIMIT 1");
    $check = mysqli_num_rows($query);

    if($check > 0) { // valid promo code
        $row = mysqli_fetch_row($query);
        $value = $row[0];
        $calls = $row[1];

        //Increment calls to this promo code
        $new_calls = intval($calls) + 1;
        $query2 = mysqli_query($db_conx, "UPDATE promotions SET calls='$new_calls' WHERE promo='$promo' LIMIT 1");

        echo $value;
        exit();

    } else { // not a valid promo code
        echo '';
        exit();
    }

    exit();

}

?>
<?php
// UPDATE CART AFTER TRANSACTION + EMAIL
if(isset($_POST["action"]) && $_POST["action"] == "update_cart_after_transaction") {

    $customer_bt = preg_replace('#[^0-9]#', '', $_POST['customer_bt']); 
    $customer_db_id = preg_replace('#[^0-9]#', '', $_POST['customer_db_id']); 
    $amount = preg_replace('#[^0-9.]#i', '', $_POST['amount']); 
    $cart_rows = preg_replace('#[^0-9,]#i', '', $_POST['cart_rows']);
    $promotion = preg_replace('#[^0-9.]#i', '', $_POST['promotion']); 

    //Get transaction ID from DB
    $query = mysqli_query($db_conx, "SELECT id FROM transactions WHERE customer='$customer_db_id' AND amount='$amount' AND bt_customer='$customer_bt' ORDER BY time DESC LIMIT 1");
    $tran_row = mysqli_fetch_row($query);
    $db_tran_id = $tran_row[0];

    $item_confirmation = ''; // for the confirmation email
    $rows_affected = explode(',', $cart_rows);
    $length = count($rows_affected);
    for($i=0; $i < $length; $i++) {

        $query = mysqli_query($db_conx, "SELECT item_id, size, state FROM carts WHERE id=".$rows_affected[$i]." LIMIT 1");
        $item_row = mysqli_fetch_row($query);
        $item_id = $item_row[0];
        $size = $item_row[1];
        $state = $item_row[2];

        mysqli_query($db_conx, "UPDATE carts SET status='payed', purchased=now(), db_transaction_id='$db_tran_id' WHERE customer='$customer_db_id' AND item_id='$item_id' AND status='pending'");

        //item info
        $query_item = mysqli_query($db_conx, "SELECT caption, pic FROM store WHERE id='$item_id' LIMIT 1");
        $rows = mysqli_fetch_row($query_item);
        $caption = $rows[0];
        $pic = $rows[1];

        $item_confirmation .= '<div class="row panel">';
        $item_confirmation .= '<div class="col-md-2 col-xs-2">';
        $item_confirmation .= '<div class="search_img" style="background:url(https://scavengg.com/store/'.$pic.') no-repeat center center;background-size:contain;"></div>';
        $item_confirmation .= '</div>';
        $item_confirmation .= '<div class="col-md-10 col-xs-10">';
        $item_confirmation .= '<b>'.$caption.'</b><br>';
        $item_confirmation .= 'Size: '.$size.'<br>';
        $item_confirmation .= 'Condition: '.$state.'';
        $item_confirmation .= '</div>';
        $item_confirmation .= '</div>';

    }

    //Get user info
    $query = mysqli_query($db_conx, "SELECT email, phone, name FROM users WHERE id='$customer_db_id' LIMIT 1");
    $rows = mysqli_fetch_row($query);
    $email = $rows[0];
    $phone = $rows[1];
    $name = $rows[2];

    // Confirmation Email
    ordersummary($email, $cart_rows, $amount, $item_confirmation, $promotion, $db_tran_id);

    // Notify admin
    $msg = 'New order on Scavengg on '.date("F d Y H:ia").'.';
    notify("felixsimard@gmail.com", $msg);
    notify("lasrywinc@yahoo.com", $msg);

    echo 'transaction_completed|'.$email.'|'.$phone.'|'.html_entity_decode($name).'|'.$db_tran_id.'';
    exit();
}
?>
<?php
if(isset($_POST["action"]) && $_POST["action"] == "save_payment_method") {

	$customer_id = preg_replace('#[^0-9]#', '', $_POST['customer_id']);
    //$uid = preg_replace('#[^0-9]#', '', $_POST['uid']);

	$sql = "UPDATE users SET bt_id='$customer_id' WHERE id='$log_id' LIMIT 1";
	mysqli_query($db_conx, $sql);

	echo ''.$customer_id.'';
	exit();


}
?>
<?php
if(isset($_POST["action"]) && $_POST["action"] == "get_payment_method") {

    //$uid = preg_replace('#[^0-9]#', '', $_POST['uid']);

    $query = mysqli_query($db_conx, "SELECT bt_id FROM users WHERE id='$log_id' LIMIT 1");
    $row = mysqli_fetch_row($query);
    $bt_id = $row[0];

    if($bt_id == "0") { // no id
        echo "no_braintree"; 
        exit();
    } else {
        echo ''.$bt_id.'';
        exit();
    }

    exit();

}
?>