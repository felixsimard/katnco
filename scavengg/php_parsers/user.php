<?php 
include_once("../php_includes/db_conx.php");
include_once("../php_includes/session.php");
include_once("../class/time_ago_class.php");
include_once("../class/phone_format.php");
include_once("../class/number_format.php");
include_once("email.php");
?> 
<?php
if(isset($_POST["action"]) && $_POST["action"] == "welcome_msg_seen") {

    if($log_id) {
        mysqli_query($db_conx, "UPDATE users SET welcome_msg='1' WHERE id='$log_id' LIMIT 1");
        echo 'welcome_msg_seen_1';   
        exit();
    } else {
        echo 'welcome_msg_seen_0';   
        exit();
    }
    exit();
}
?>
<?php
if(isset($_POST["action"]) && $_POST["action"] == "check_welcome_msg") {

    // Check welcome_msg
    if($log_id) {
        $query = mysqli_query($db_conx, "SELECT welcome_msg FROM users WHERE id='$log_id' LIMIT 1");
        $row = mysqli_fetch_row($query);
        if($row[0] == 1) {
            echo 'showed';
            exit();
        } else {
            echo 'show';
            exit();
        }
    } else {
        echo 'show';
        exit();
    }
    exit();
}
?>
<?php
if(isset($_POST["action"]) && $_POST["action"] == "save_delivery_address") {

    $delivery = htmlentities($_POST['delivery']);
	$delivery = mysqli_real_escape_string($db_conx, $delivery);

    if($delivery != "") {
        mysqli_query($db_conx, "UPDATE users SET delivery='$delivery' WHERE id='$log_id' LIMIT 1");
    }

    echo $delivery;
    exit();

}
?>
<?php
if(isset($_POST["action"]) && $_POST["action"] == "delete_from_cart") {

    $items_id = $_POST["items_to_delete"];
    $items_to_delete = explode(",", $items_id);
    $length = sizeof($items_to_delete);
    for($i=0; $i < $length; $i++) {
        $item_id = $items_to_delete[$i];
        $or .= 'item_id='.$item_id.'';
        if($i < ($length - 1)) {
            $or .= ' OR ';
        }
    } 
    mysqli_query($db_conx, "DELETE FROM carts WHERE (".$or.") AND customer='$log_id'");
    
    echo 'items_deleted';
    exit();
}
?>
<?php
if(isset($_POST["action"]) && $_POST["action"] == "cart_content") {

    $uid = preg_replace('#[^0-9]#', '', $_POST["uid"]);

    if($uid != "") {
        $query = mysqli_query($db_conx, "SELECT id FROM carts WHERE customer='$uid' AND status='pending'");
        $numrows = mysqli_num_rows($query);
        if($numrows > 0) {
            echo 1;
            exit();
        } else {
            echo 0;
            exit();
        }
    } else {
        echo 0;
        exit();
    }

}
?>
<?php
if(isset($_POST["action"]) && $_POST["action"] == "check_activated") {

    $context = $_POST["context"]; // if coming from coutdown launch

    if($context == "") {
        $context = "normal";
    }

    //first see if they have a session log_id defined
    if($log_id != "") { // account exists

        //check Activated, Phone and Braintree
        $query = mysqli_query($db_conx, "SELECT phone, bt_id, activated FROM users WHERE id='$log_id' LIMIT 1");
        $row = mysqli_fetch_row($query);
        $phone = $row[0];
        $bt = $row[1];
        $activated = $row[2];

        if($activated == 1) {
            if($context == "countdown_launch") {
                if($phone != "") {
                    echo 'activated_for_launch';
                    exit();
                } else {
                    echo 'not_activated_no_phone';
                    exit();
                }
            } else {
                if($phone == "" && $bt == 0) { //missing bt and phone
                    echo 'not_activated_no_bt_no_phone';
                    exit();
                } else if($phone == "" && $bt != 0) { //missing phone
                    echo 'not_activated_no_phone';
                    exit();
                } else if($phone != "" && $bt == 0) { //missing bt
                    echo 'not_activated_no_bt';
                    exit();
                } else { //missing nothing
                    echo 'activated';
                    exit();
                }
            }

        } else {
            echo 'no_activated_not_allowed';
            exit();
        }

    } else { // no account (not logged in with Facebook)
        echo 'not_activated_no_fb';
        exit();
    }
    exit();
}
?>
<?php
if(isset($_POST["action"]) && $_POST["action"] == "check_phone") {

    $uid = preg_replace('#[^0-9]#', '', $_POST["uid"]);

    $query = mysqli_query($db_conx, "SELECT phone, welcome_sms FROM users WHERE id='$log_id' LIMIT 1");
    $row = mysqli_fetch_row($query);
    $phone = $row[0];
    $hasreceived = $row[1];

    if($phone == "" || $phone == "0") {
        echo 'no_phone|';
        exit();
    } else {
        if($hasreceived == '0') { 
            echo ''.$phone.'|send_sms';
            exit();
        } else {
            echo ''.$phone.'|';
            exit();
        }

    }

}
?>
<?php
if(isset($_POST["action"]) && $_POST["action"] == "save_phone") {

    $phone = preg_replace('#[^0-9]#', '', $_POST["phone"]);
    $uid = preg_replace('#[^0-9]#', '', $_POST["uid"]);
    $action2 = $_POST["action2"];

    if($phone == "" || strlen($phone) != 10) {
        echo '';
        exit();
    } else {
        mysqli_query($db_conx, "UPDATE users SET phone='$phone' WHERE id='$log_id' LIMIT 1");

        if($action2 == "welcome_sms_done") {
            mysqli_query($db_conx, "UPDATE users SET welcome_sms='1' WHERE id='$log_id' LIMIT 1");
        }

        echo 'phone_saved';
        exit();
    }

}
?>
<?php
if(isset($_POST["action"]) && $_POST["action"] == "view_cart") {

    $uid = preg_replace('#[^0-9]#', '', $_POST["uid"]);


    if($uid == "" && $log_id == "") {
        echo '';
        exit();
    } else {    
        //Check if user cart is fully empty
        $query_cart = mysqli_query($db_conx, "SELECT * FROM carts WHERE customer='$log_id' ORDER BY added DESC");
        $numrows_cart = mysqli_num_rows($query_cart);
        //Check if also no special requests
        $query_requests = mysqli_query($db_conx, "SELECT * FROM requests WHERE uid='$log_id' AND status='pending' ORDER BY added DESC");
        $numrows_requests = mysqli_num_rows($query_requests);

        if($numrows_cart < 1 && $numrows_requests < 1) { // empty cart and no requests
            $cart .= '<div class="cart_header"><div class="profile_pic"></div></div>';
            $cart .= '<div class="row">';
            $cart .= '<div class="col-md-12">';
            $cart .= 'Nothing added to cart or purchased yet.';
            $cart .= '</div>';
            $cart .= '</div>';
            $cart .= '<div id="cart_purchases_div"></div>'; // create the element by add nothing to it
        } else {

                //User Info
                $query_user = mysqli_query($db_conx, "SELECT name, bt_id, picture, delivery FROM users WHERE id='$log_id' LIMIT 1");
                $rows_user = mysqli_fetch_row($query_user);
                $name = $rows_user[0];
                $bt_id = $rows_user[1];
                $avatar = $rows_user[2];
                $delivery = $rows_user[3]; // delivery address

            while($row = mysqli_fetch_array($query_cart, MYSQLI_ASSOC)) {
                $cart_id = $row["id"];
                $item_id = $row["item_id"];
                $size = $row["size"];
                $item_price = $row["price"];
                $state = $row["state"];
                $status = $row["status"];
                $date_purchased = $row["purchased"];

                /*if($state == 'new') {
                    $price = 'min_new';
                    $condition = 'new';
                } else if($state == 'newish') {
                    $price = 'min_newish';
                    $condition = 'new, no tags';
                } else {
                    $price = 'min_used';
                    $condition = 'used';
                }*/

                $query_item = mysqli_query($db_conx, "SELECT caption, pic FROM store WHERE id='$item_id' LIMIT 1");
                $row_item = mysqli_fetch_row($query_item);
                $caption = $row_item[0];
                $pic = $row_item[1];

                if($status == 'pending') {

                    $cart_pending .= '<div id="cart_item_'.$cart_id.'_'.$item_id.'" class="row hover cart_rows cart_pending cart_item_unselected" onclick="selectItem(\''.$cart_id.'\',\''.$item_id.'\')">';
                    $cart_pending .= '<div class="col-md-2 col-xs-2" style="text-align:center;">';
                    $cart_pending .= '<img class="cart_pic" src="https://scavengg.com/store/'.$pic.'">';
                    $cart_pending .= '</div>';
                    $cart_pending .= '<div class="col-md-10 col-xs-10 cart_text">';
                    $cart_pending .= '<b>'.$caption.'</b><br>';
                    $cart_pending .= 'Size: '.$size.'<br>';
                    $cart_pending .= 'Condition: new<br>';
                    $cart_pending .= '<span id="item_price_'.$item_id.'">'.$item_price.'</span>$'; // need to keep item_id on this line (not cart_id)
                    $cart_pending .= '</div>';
                    $cart_pending .= '</div>';

                } else {

                    if($status == "payed") {
                        $item_status = '<span style="font-size:1.2em;color:#61d395;">Processing order</span>';
                    } else if($status == "delivered") {
                        $item_status = '<span style="font-size:1.2em;color:#61d395;">Delivered</span>';
                    } else if($status == "refunded") {
                        $item_status = '<span style="font-size:1.2em;color:#ff5050;">Refunded</span>';
                    } else { // error
                        $item_status = '<span style="font-size:1.2em;color:#ff5050;">Transaction Error</span>';
                    }

                    $cart_payed .= '<div class="row hover cart_rows">';
                    $cart_payed .= '<div class="col-md-2 col-xs-2" style="text-align:center;">';
                    $cart_payed .= '<img class="cart_pic" src="https://scavengg.com/store/'.$pic.'">';
                    $cart_payed .= '</div>';
                    $cart_payed .= '<div class="col-md-10 col-xs-10 cart_text">';
                    $cart_payed .= '<b>'.$caption.'</b><br>';
                    $cart_payed .= 'Size: '.$size.'<br>';
                    $cart_payed .= 'Condition: '.$condition.'<br>';
                    $cart_payed .= 'Purchased on '.date("M j H:i A", strtotime($date_purchased)).'<br>';
                    $cart_payed .= ''.$item_status.'<br>';
                    $cart_payed .= '</div>';
                    $cart_payed .= '</div>';

                }


            }

            //Load Custom Requests
            if($numrows_requests > 0) {
                while($row = mysqli_fetch_array($query_requests, MYSQLI_ASSOC)) {
                    $desc = $row["description"];
                    $size = $row["size"];
                    $pic = $row["picture"];
                    $brand = $row["brand"];
                    $season = $row["season"];
                    $added = $row["added"];

                    if($pic == "") {
                        $request_pic_link = 'https://scavengg.com/photos/logo.png';
                    } else {
                        $request_pic_link = 'https://scavengg.com/requests/'.$pic.'';
                    }

                    $requests .= '<div class="row hover cart_rows">';
                    $requests .= '<div class="col-md-2 col-xs-2" style="text-align:center;">';
                    $requests .= '<img class="request_pic" src="'.$request_pic_link.'">';
                    $requests .= '</div>';
                    $requests .= '<div class="col-md-10 col-xs-10 cart_text">';
                    $requests .= '<b>'.$desc.'</b><br>';
                    $requests .= 'Size: '.$size.'<br>';
                    if($brand) {
                        $requests .= 'Brand: '.$brand.'<br>';
                    }
                    if($season) {
                        $requests .= 'Season: '.$season.'<br>';
                    }
                    $requests .= 'Requested on '.date("M j H:i A", strtotime($added)).'<br>';
                    $requests .= '</div>';
                    $requests .= '</div>';
                }
            } else {
                $requests = "";
            }

                $cart .= '<div class="cart_header"><div class="profile_pic"></div></div>';
                $cart .= '<div id="cart_header" class="cart_header">';
                if($cart_pending != "") {
                    $cart .= '<button id="remove_from_cart" class="remove_from_cart" onclick="removefromCart()">Remove</button> <button id="review_cart" class="review_cart" onclick="reviewCart(\''.$bt_id.'\', \''.$uid.'\')">Checkout</button></div><hr>';
                    $cart .= ''.$cart_pending.'';
                } else {
                    $cart .= '</div><hr>';
                    $cart .= '<div class="row">';
                    $cart .= '<div class="col-md-12">';
                    $cart .= 'Your cart is empty.';
                    $cart .= '</div>';
                    $cart .= '</div>';
                }
                $cart .= '<div id="try_out_custom_order"><div class="cart_header"><hr><b>Custom Requests</b></div>';
                if($requests != "") {
                    $cart .= ''.$requests.'';
                } else {
                    $cart .= '<div class="row">';
                    $cart .= '<div class="col-md-12">';
                    $cart .= '<button class="review_cart" style="float:left;" onclick="flyout(\'custom_request\')">Try it out</button>';
                    $cart .= '</div>';
                    $cart .= '</div></div>';
                }
                $cart .= '</div>'; // closing div id="try_out_custom_order"
                $cart .= '<hr><div class="cart_header"><b>Delivery Address</b></div>';
                $cart .= '<span style="font-size:22px;color:#61d395;">FREE SHIPPING</span>';
                $cart .= '<input id="delivery_address" class="phone_input" value="'.$delivery.'" placeholder="Enter delivery address">';
                $cart .= '<hr><div id="cart_purchases_div"><div class="cart_header"><b>Previous Purchases</b></div>';
                if($cart_payed != "") {
                    $cart .= ''.$cart_payed.'';
                } else {
                    $cart .= '<div class="row">';
                    $cart .= '<div class="col-md-12">';
                    $cart .= 'No purchases yet.';
                    $cart .= '</div>';
                    $cart .= '</div></div>';
                }
                $cart .= '</div>';//close cart_purchases_div here
                
                //Add a div for checkout process
                $cart .= '<div id="checkout_details" class="col-md-12"></div>';

        }

        echo ''.$cart.'|'.$bt_id.'|'.$uid.'';
        exit();
    }

    exit();

}
?>