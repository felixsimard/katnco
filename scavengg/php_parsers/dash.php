<?php 
include_once("../php_includes/db_conx.php");
include_once("../class/time_ago_class.php");
include_once("../class/phone_format.php");
include_once("../class/number_format.php");
/* COLOR CODE */
$yellow = '#e6e600';
$green = '#61d395';
$blue = '#99ccff';
$red = '#ff5050'; 
$orange = '#ff9900';
?> 
<?php
// NEWSLETTER 
if(isset($_POST["action"]) && ($_POST["action"] == "newsletter_preview" || $_POST["action"] == "newsletter_send")) { 

    $news_title = filter_var($_POST["news_title"], FILTER_SANITIZE_STRING);
    $news_text = filter_var($_POST["news_text"], FILTER_SANITIZE_STRING);
    $hasPicture = $_POST["hasPicture"];;

    if($hasPicture == "true") {
        //GET EMAIL THAT HAS THE MOST RECENT 'added' field
        $query = mysqli_query($db_conx, "SELECT id, picture FROM news ORDER BY added DESC LIMIT 1");
        $row = mysqli_fetch_row($query);
        $news_id = $row[0];
        $news_picture = $row[1];
    }

    $news .= '<div class="news_logo_box"><a class="news_a" href="https://scavengg.com/"><div class="news_logo"></div></a></div>';
    $news .= '<div class="news_panel">';
    $news .= '<div class="news_logo_title">'.$news_title.'</div>';
    $news .= '<div class="date_time">'.date("Y-m-d").'</div>';
    if($hasPicture == 'true') {
        $news .= '<div class="media_box"><img id="news_img" class="media" src="https://scavengg.com/news/'.$news_picture.'"></div>';
    }
    $news .= '<div class="text_content">'.$news_text.'</div>';
    $news .= '<a class="news_a" href="https://scavengg.com/"><button class="news_btns">scavengg.com</button></a>';
    $news .= '</div>';
    $news .= '<div class="news_disclaimer">';
    $news .= '&copy;Scavengg, All Rights Reserved, '.date("Y").'<br>Montreal, Qc, Canada';
    $news .= '</div>';

    if($_POST["action"] == "newsletter_preview") {
        $content .= '<button id="news_letter_btn" class="dash_btns" onclick="flyout(\'newsletter_send\')">Send Newsletter</button><button class="red_dash_btns" onclick="flyout(\'close\')">Edit</button><hr>';
        $content .= $news;
        echo $content;
        exit();
    } else {

        if($hasPicture == "true") { // row already exists

            mysqli_query($db_conx, "UPDATE news SET title='$news_title', message='$news_text' WHERE id='$news_id' LIMIT 1");
        
        } else { // no rows, no picture   
            //Insert news into DB
            $sql = "INSERT INTO news (title, message, added) VALUES ('$news_title','$news_text',now())";
            $query = mysqli_query($db_conx, $sql);
            $news_id = mysqli_insert_id($db_conx);
        }

        $content = file_get_contents("../emails/weekly.php"); // will includes the stylings
        $content .= $news;
        $news .= '</body>';

        // NEWSLETTER 
        $from = "Scavengg <scavengg@gmail.com>";
        $subject = ''.$news_title.'';

        $headers = "From: $from\r\n". 
                "MIME-Version: 1.0" . "\r\n" . 
                "Content-type: text/html; charset=UTF-8" . "\r\n";
        
        // Get all email accounts
        $query = mysqli_query($db_conx, "SELECT * FROM users WHERE email<>'' OR email<>'undefined' OR email<>' '");
        while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
            $email = $row["email"];
            if($email != "undefined" || $email != "") {
                $email_accounts .= ''.$email.',';
            }
        }
        
        //$email_accounts = 'felixsimard@gmail.com,lasrywinc@yahoo.com';
        $emails_array = explode(",", $email_accounts);
        for($i = 0; $i < count($emails_array) - 1; $i++) {
            mail($emails_array[$i], $subject, $content, $headers); 
        }
        
        echo 'newsletter_sent';
        exit();
    }
    exit();
}
?>
<?php
// TOGGLE STATUS
if(isset($_POST["action"]) && $_POST["action"] == "toggle_status") {

    $table = htmlentities($_POST['table']);
	$table = mysqli_real_escape_string($db_conx, $table);
    $col = htmlentities($_POST['col']);
	$col = mysqli_real_escape_string($db_conx, $col);
    $id = preg_replace('#[^0-9]#', '', $_POST['id']);
    $value = preg_replace('#[^a-z0-9]#i', '', $_POST['value']);

    mysqli_query($db_conx, "UPDATE $table SET $col='$value' WHERE id='$id' LIMIT 1");

    // Get customer id
    $query = mysqli_query($db_conx, "SELECT customer FROM $table WHERE id='$id' LIMIT 1");
    $row = mysqli_fetch_row($query);

    echo $row[0]; // customer ID
    exit();
}
?>
<?php
// ADD AMOUNT PAYED BY SCAVENGG
if(isset($_POST["action"]) && $_POST["action"] == "save_amount_payed_by_scavengg") {

    $tran_id = preg_replace('#[^0-9]#', '', $_POST['tran_id']);
    $amount_payed_by_scavengg = preg_replace('#[^0-9.]#i', '', $_POST['amount_payed_by_scavengg']);

    if($tran_id != "" || $amount_payed_by_scavengg != "") {
        mysqli_query($db_conx, "UPDATE transactions SET payed_by_scavengg='$amount_payed_by_scavengg' WHERE id='$tran_id' LIMIT 1");
        echo "amount_saved";
        exit();
    } else {
        echo 'error_saving_amount_payed_by_scavengg';
        exit();
    }

}
?>
<?php
// EMAIL NOTIFICATIONS
if(isset($_POST["action"]) && $_POST["action"] == "send_email_notification") {

    $msg = htmlentities($_POST['msg']);
    $msg = mysqli_real_escape_string($db_conx, $msg);
    $to = htmlentities($_POST['to']);
    $to = mysqli_real_escape_string($db_conx, $to);
    
    function email_notify($to, $msg) {
        $notify .= '<div class="center">';
        $notify .= '<div class="fat_text">Notification</div>';
        $notify .= '<div class="slim_text">'.$msg.'</div>';
        $notify .= '<a href="https://scavengg.com/"><button class="btns">scavengg.com</button></a>';
        $notify .= '</div>';
        $notify .= '</div>';
        $notify .= '<div class="disclaimer">';
        $notify .= '&copy;Scavengg, All Rights Reserved, '.date("Y").'<br>Montreal, Qc, Canada';
        $notify .= '</div>';
        $notify .= '</body>';
        //$notify .= '</html>';

        $content = file_get_contents("../emails/notify.php");
        $content .= $notify;
                    
        $from = "Scavengg <scavengg@gmail.com>";
        $subject = 'Notification from Scavengg';

        $headers = "From: $from\r\n". 
                "MIME-Version: 1.0" . "\r\n" . 
                "Content-type: text/html; charset=UTF-8" . "\r\n";

        mail($to, $subject, $content, $headers); 

    }

    // Send Email Notification
    email_notify($to, $msg);

    echo 'email_sent';
    exit();

}
?>
<?php
// GET ACTIVITY LOG
if(isset($_POST["action"]) && $_POST["action"] == "get_activity_logs") {

    $past_time = preg_replace('#[^a-z0-9 ]#i', '', $_POST['past_time']);

    if($past_time) {
        if($past_time == "year") {
            $past_hours = 8760;
        } else if($past_time == "half_year") {
             $past_hours = 4380;
        } else if($past_time == "month") {
            $past_hours = 730;
        } else if($past_time == "week") {
            $past_hours = 168;
        } else { // in hours
            $past_hours = intval($past_time);
        }
    } else {
        $past_hours = 168;
    }
    
    /*
    $sql = "SELECT * FROM (
            SELECT c.customer AS customer_id, c.status AS cart_status, c.added AS added, 'carts' AS ComesFrom FROM carts c UNION ALL
            SELECT r.uid AS customer_id, r.status AS request_status, r.added AS added, 'requests' AS ComesFrom FROM requests r UNION ALL
            SELECT s.searcher AS customer_id, s.item_id AS clicked_item, s.time AS added, 'searches' AS ComesFrom FROM searches s UNION ALL
            SELECT u.id AS customer_id, u.email AS user_email, u.lastlogin AS added, 'users' AS ComesFrom FROM users u)
            foo WHERE added > NOW() - INTERVAL $past_hours HOUR ORDER BY added DESC";
    */

    $sql = "SELECT * FROM (
        SELECT c.customer AS customer_id, c.status AS cart_status, c.added AS added, 'carts' AS ComesFrom FROM carts c UNION ALL
        SELECT r.uid AS customer_id, r.status AS request_status, r.added AS added, 'requests' AS ComesFrom FROM requests r UNION ALL
        SELECT u.id AS customer_id, u.email AS user_email, u.lastlogin AS added, 'users' AS ComesFrom FROM users u)
        foo WHERE added > NOW() - INTERVAL $past_hours HOUR ORDER BY added DESC";

    $query = mysqli_query($db_conx, $sql);
    $numrows = mysqli_num_rows($query);

    if($numrows > 0) {
        $active_counter = 0;
        while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
            // Know from which table the data comes from
            $comesfrom = $row["ComesFrom"];
            $customer_id = $row["customer_id"];
            $added = $row["added"];

            //Get customer info + Format Ago Time
            $query_customer = mysqli_query($db_conx, "SELECT name FROM users WHERE id='$customer_id' LIMIT 1");
            $customer_rows = mysqli_fetch_row($query_customer);
            $customer_name = $customer_rows[0];
            if($customer_name == "") {
                $customer_name = 'Visitor';
            }
    
            $ago_added = time_elapsed_string($added);
            if($ago_added == 'just now') {
                $added_ago = $ago_added;
            } else {
                $added_ago = ''.$ago_added.' ago';
            }

            //Carts
            if($comesfrom == "carts") {
                if($row["cart_status"] == 'pending') {
                    $event_color = $yellow;
                } else if ($row["cart_status"] == 'payed' || $row["cart_status"] == 'delivered') {
                    $event_color = $blue;
                } else {
                    $event_color = $red;
                }
                $event = '<span style="color:'.$event_color.';">order '.$row["cart_status"].'</span>';
            }
            //Requests
            if($comesfrom == "requests") {
                $event = '<span style="color:'.$orange.'">request '.$row["request_status"].'</span>';
            }
            /*Searches
            if($comesfrom == "searches") {  
                $event = 'viewed an item';
            } */
            //Users
            if($comesfrom == "users") {
                $event = '<span style="color:'.$green.';">active</span>';
                $active_counter += 1;
            }

            $logs .= '<tr>';
            $logs .= '<td>'.$customer_name.'</td>';
            $logs .= '<td>'.$event.'</td>';
            $logs .= '<td>'.$added_ago.'</td>';
            $logs .= '</tr>';
        }

    } else {
        $logs .= '<tr>';
        $logs .= '<td>Nothing to show</td>';
        $logs .= '<td></td>';
        $logs .= '<td></td>';
        $logs .= '</tr>';
    }

    $activity .= 'Active ('.$active_counter.')';
    $activity .= '<table class="table table-hover console_table">';
    $activity .= '<tr>';
    $activity .= '<th>Customer</th>';
    $activity .= '<th>Activity</th>';
    $activity .= '<th>Time</th>';
    $activity .= '</tr>';
    $activity .= ''.$logs.'';
    $activity .= '</table>';

    echo $activity;
    exit();

}

?>
<?php
// ADD NEW PROMO CODE
if(isset($_POST["action"]) && $_POST["action"] == "add_promo") {

    $promo = htmlentities($_POST['promo']);
	$promo = mysqli_real_escape_string($db_conx, $promo);
    $value = preg_replace('#[^0-9]#', '', $_POST['value']);

    if($promo != "" || $value != "") {
        $sql = "INSERT INTO promotions (promo, value, active, added) 
                VALUES ('$promo','$value','1',now())";
        $query = mysqli_query($db_conx, $sql);
        $pid = mysqli_insert_id($db_conx);
        echo "promo_saved";
        exit();
    } else {
        echo 'error_adding_promo';
        exit();
    }

}
?>
<?php
// GET PHONE NUMBER FOR NOTIFICATION
if(isset($_POST["action"]) && $_POST["action"] == "get_phone_number") {

    $uid = preg_replace('#[^0-9]#', '', $_POST['uid']);

    $query = mysqli_query($db_conx, "SELECT phone, name FROM users WHERE id='$uid' LIMIT 1");
    $rows = mysqli_fetch_row($query);
    $phone = $rows[0];
    $name = html_entity_decode($rows[1]);

    echo ''.$phone.'|'.$name.'';
    exit();
}
?>
<?php
// GET NUMBERS FOR EACH SECTION
if(isset($_POST["action"]) && $_POST["action"] == "get_numbers") {

    //Num Requests
    $query1 = mysqli_query($db_conx, "SELECT id FROM requests WHERE status='pending'");
    $num_requests = mysqli_num_rows($query1);

    //Num Orders
    $query2 = mysqli_query($db_conx, "SELECT id FROM carts WHERE status='pending' OR status='payed'");
    $num_orders = mysqli_num_rows($query2);

    //Num Sales
    $query3 = mysqli_query($db_conx, "SELECT id FROM carts WHERE status='delivered'");
    $num_sales = mysqli_num_rows($query3);

    //Num Items
    $query4 = mysqli_query($db_conx, "SELECT id FROM store");
    $num_items = mysqli_num_rows($query4);

    //Num Customers
    $query5 = mysqli_query($db_conx, "SELECT id FROM users WHERE activated='1'");
    $num_customers = mysqli_num_rows($query5);

    //Num Transactions
    $query6 = mysqli_query($db_conx, "SELECT id FROM transactions");
    $num_transactions = mysqli_num_rows($query6);

    echo ''.$num_requests.'|'.$num_orders.'|'.$num_sales.'|'.$num_items.'|'.$num_customers.'|'.$num_transactions.'';
    exit();

}
?>
<?php
// GET ALL PROMOTIONS
if(isset($_POST["action"]) && $_POST["action"] == "get_promotions") {

    $query = mysqli_query($db_conx, "SELECT * FROM promotions ORDER BY added DESC");
    $numrows = mysqli_num_rows($query);
    if($numrows < 1) {
        $promo_rows .= '<tr><td>No promotions available.</td>';
        $promo_rows .= '<td></td>';
        $promo_rows .= '<td></td>';
        $promo_rows .= '<td></td>';
        $promo_rows .= '<td></td>';
        $promo_rows .= '</tr>';
    } else {    

        while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
            $pid = $row["id"];
            $promotion = $row["promo"];
            $value = $row["value"];
            $calls = $row["calls"];
            $status = $row["active"];
            $added = $row["added"];

            if($status == 1) {
                $active = '<label class="switch"><input type="checkbox" onclick="switchToggle(this, \'promotions\', \'active\', \''.$pid.'\')" checked><span class="slider round"></span></label>';
            } else {
                $active = '<label class="switch"><input type="checkbox" onclick="switchToggle(this, \'promotions\', \'active\', \''.$pid.'\')"><span class="slider round"></span></label>';
            }

            $promo_rows .= '<tr>';
            $promo_rows .= '<td>'.$pid.'</td>';
            $promo_rows .= '<td>'.$promotion.'</td>';
            $promo_rows .= '<td>'.$value.'</td>';
            $promo_rows .= '<td>'.$calls.'</td>';
            $promo_rows .= '<td>'.date("M j H:i A", strtotime($added)).'</td>';
            $promo_rows .= '<td>'.$active.'</td>';
            $promo_rows .= '</tr>';

        }

    }

        $promo .= '<table class="table table-hover console_table">';
        $promo .= '<tr>';
        $promo .= '<th>#</th>';
        $promo .= '<th>Promo</th>';
        $promo .= '<th>Value</th>';
        $promo .= '<th>Calls</th>';
        $promo .= '<th>Added</th>';
        $promo .= '<th>Active</th>';
        $promo .= '</tr>';
        $promo .= ''.$promo_rows.'';
        $promo .= '</table><hr>';
        $promo .= '<button class="dash_btns" onclick="flyout(\'add_promo\')">Add promo</button>';

    echo ''.$promo.'';
    exit();

}
?>

<?php
// GET ALL REQUESTS
if(isset($_POST["action"]) && $_POST["action"] == "get_requests") {

    $query = mysqli_query($db_conx, "SELECT * FROM requests ORDER BY added DESC");
    $numrows = mysqli_num_rows($query);
    if($numrows < 1) {
        $requests_rows .= '<tr><td>No custom requests.</td>';
        $requests_rows .= '<td></td>';
        $requests_rows .= '<td></td>';
        $requests_rows .= '<td></td>';
        $requests_rows .= '<td></td>';
        $requests_rows .= '<td></td>';
        $requests_rows .= '<td></td>';
        $requests_rows .= '<td></td>';
        $requests_rows .= '<td></td>';
        $requests_rows .= '</tr>';
    } else {    

        while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
            $rid = $row["id"];
            $uid = $row["uid"];
            $caption = $row["description"];
            $size = $row["size"];
            $pic = $row["picture"];
            $brand = $row["brand"];
            $season = $row["season"];
            $status = $row["status"];
            $added = $row["added"];

            if($pic == "") {
                $picture = 'https://scavengg.com/photos/default_pic.png';
            } else {
                $picture = 'https://scavengg.com/requests/'.$pic.'';
            }

            if($status == "completed") {
                $req_status = '<label class="switch"><input type="checkbox" onclick="switchToggle(this, \'requests\', \'status\', \''.$rid.'\', \''.$uid.'\')" checked><span class="slider round"></span></label>';
            } else { //pending
                $req_status = '<label class="switch"><input type="checkbox" onclick="switchToggle(this, \'requests\', \'status\', \''.$rid.'\', \''.$uid.'\')"><span class="slider round"></span></label>';
            }

            //Get customer info
            $query_customer = mysqli_query($db_conx, "SELECT name FROM users WHERE id='$uid' LIMIT 1");
            $customer_rows = mysqli_fetch_row($query_customer);
            $customer_name = $customer_rows[0];

            $requests_rows .= '<tr>';
            $requests_rows .= '<td>'.$rid.'</td>';
            $requests_rows .= '<td><div class="dash_pic" style="background:url('.$picture.') no-repeat center;background-size:contain;"></div></td>';
            $requests_rows .= '<td>'.$customer_name.' #'.$uid.'</td>';
            $requests_rows .= '<td>'.$caption.'</td>';
            $requests_rows .= '<td>'.$brand.'</td>';
            $requests_rows .= '<td>'.$size.'</td>';
            $requests_rows .= '<td>'.$season.'</td>';
            $requests_rows .= '<td>'.date("M j H:i A", strtotime($added)).'</td>';
            $requests_rows .= '<td>'.$req_status.'</td>';
            $requests_rows .= '</tr>';

        }

    }

        $requests .= '<table class="table table-hover console_table">';
        $requests .= '<tr>';
        $requests .= '<th>#</th>';
        $requests .= '<th>Item</th>';
        $requests .= '<th>From</th>';
        $requests .= '<th>Description</th>';
        $requests .= '<th>Brand</th>';
        $requests .= '<th>Size</th>';
        $requests .= '<th>Season</th>';
        $requests .= '<th>Added</th>';
        $requests .= '<th>Completed</th>';
        $requests .= '</tr>';
        $requests .= ''.$requests_rows.'';
        $requests .= '</table>';

    echo ''.$requests.'';
    exit();

}
?>
<?php
// GET TRANSACTION DETAILS
if(isset($_POST["action"]) && $_POST["action"] == "get_transaction_details") {

    $tran_id = preg_replace('#[^0-9]#', '', $_POST['tran_id']);

    // Get amount payed by scavengg for that transaction
    $query = mysqli_query($db_conx, "SELECT amount, payed_by_scavengg FROM transactions WHERE id='$tran_id' LIMIT 1");
    $tran_row = mysqli_fetch_row($query);
    $tran_amount = $tran_row[0];
    $amount_payed_by_us = $tran_row[1];

    $query = mysqli_query($db_conx, "SELECT * FROM carts WHERE db_transaction_id='$tran_id'");
    $numrows = mysqli_num_rows($query);
    if($numrows < 1) { // this should never be true
        $tran_rows .= '<tr><td>No items associated to this transaction...</td>';
        $tran_rows .= '<td></td>';
        $tran_rows .= '<td></td>';
        $tran_rows .= '<td></td>';
        $tran_rows .= '<td></td>';
        $tran_rows .= '<td></td>';
        $tran_rows .= '<td></td>';
        $tran_rows .= '<td></td>';
        $tran_rows .= '</tr>';
    } else {

        while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
            $cart_id = $row["id"];
            $item_id = $row["item_id"];
            $db_customer_id = $row["customer"];
            $size = $row["size"];
            $condition = $row["state"];
            $purchased = $row["purchased"];
            $status = $row["status"];
            $added = $row["added"];

            //Get item info
            $query_item = mysqli_query($db_conx, "SELECT caption, pic, brand FROM store WHERE id='$item_id' LIMIT 1");
            $rows = mysqli_fetch_row($query_item);
            $item_caption = $rows[0];
            $item_pic = $rows[1];
            $item_brand = $rows[2];

            //Get customer info
            $query_customer = mysqli_query($db_conx, "SELECT name FROM users WHERE id='$db_customer_id' LIMIT 1");
            $customer_rows = mysqli_fetch_row($query_customer);
            $customer_name = $customer_rows[0];

            if($status == "pending") { // should never be yellow
                $color = 'style="background:'.$yellow.';"';
            } else if($status == "payed") {
                $color = 'style="background:'.$green.';"';
            } else if($status == "delivered") {
                $color = 'style="background:'.$blue.';"';
            } else {
                $color = 'style="background:'.$red.';"';
            }

            $tran_rows .= '<tr>';
            $tran_rows .= '<td '.$color.'>'.$cart_id.'</td>';
            $tran_rows .= '<td><div class="dash_pic" style="background:url(https://scavengg.com/store/'.$item_pic.') no-repeat center;background-size:contain;"></div></td>';
            $tran_rows .= '<td>'.$item_caption.' #'.$item_id.'</td>';
            $tran_rows .= '<td>'.$customer_name.' #'.$db_customer_id.'</td>';
            $tran_rows .= '<td>'.$item_brand.'</td>';
            $tran_rows .= '<td>'.$size.'</td>';
            $tran_rows .= '<td>'.$condition.'</td>';
            $tran_rows .= '<td>'.$status.'</td>';
            $tran_rows .= '<td>'.date("M j H:i A", strtotime($purchased)).'</td>';
            $tran_rows .= '</tr>';

        }
    }   
        $transaction .= '<div>Transaction ('.$tran_amount.'$) #'.$tran_id.'</div>';
        $transaction .= '<hr>';
        if($status == "refunded") {
            $transaction .= '<div style="font-size:20px;color:'.$red.';">Refunded</div>';
        } else {
            $transaction .= '<div style="font-size:16px;">Total amount payed by Scavengg for this order:</div>';
            if($amount_payed_by_us != 0) {
                $transaction .= '<input id="payed_by_us_input" class="dash_inputs_half" value="'.$amount_payed_by_us.'" placeholder="Payed by Scavengg" maxlength="10"> &nbsp; <button class="dash_btns" onclick="payedByUs(\''.$tran_id.'\', \'payed_by_us_input\')">Save</button>';
            } else {
                $transaction .= '<input id="payed_by_us_input" class="dash_inputs_half" placeholder="Payed by Scavengg" maxlength="10"> &nbsp; <button class="dash_btns" onclick="payedByUs(\''.$tran_id.'\', \'payed_by_us_input\')">Save</button>';
            }
        }
        $transaction .= '<table class="table table-hover console_table">';
        $transaction .= '<tr>';
        $transaction .= '<th>#</th>';
        $transaction .= '<th>Item</th>';
        $transaction .= '<th>Description</th>';
        $transaction .= '<th>Customer</th>';
        $transaction .= '<th>Brand</th>';
        $transaction .= '<th>Size</th>';
        $transaction .= '<th>Condition</th>';
        $transaction .= '<th>Status</th>';
        $transaction .= '<th>Purchased</th>';
        $transaction .= '</tr>';
        $transaction .= ''.$tran_rows.'';
        $transaction .= '</table>';

        echo $transaction;
        exit();

}
?>
<?php
// GET REVENUES (TRANSACTIONS)
if(isset($_POST["action"]) && $_POST["action"] == "get_transactions") {

    $query = mysqli_query($db_conx, "SELECT * FROM transactions ORDER BY time DESC");
    $numrows = mysqli_num_rows($query);
    if($numrows < 1) { // no transactions yet
        $revenu_rows .= '<tr><td>No transactions yet.</td>';
        $revenu_rows .= '<td></td>';
        $revenu_rows .= '<td></td>';
        $revenu_rows .= '<td></td>';
        $revenu_rows .= '<td></td>';
        $revenu_rows .= '<td></td>';
        $revenu_rows .= '<td></td>';
        $revenu_rows .= '<td></td>';
        $revenu_rows .= '</tr>';
    } else {

        $revenues = 0;
        $costs = 0;
        $bt_fee = 0.029;
        while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
            $tran_id = $row["id"];
            $db_customer_id = $row["customer"];
            $amount = $row["amount"];
            $payed_by_scavengg = $row["payed_by_scavengg"];
            $bt_customer = $row["bt_customer"];
            $bt_transaction = $row["bt_transaction"];
            $status = $row["status"];
            $time = $row["time"];

            $status_select = '';
            if($status == "approved") {
                $color = ''.$green.'';
                $status_select .= '<option value="approved" selected>approved</option>';
                $status_select .= '<option value="declined">declined</option>';
                $status_select .= '<option value="refunded">refunded</option>';
                $status_select .= '<option value="error">error</option>';

                // Add to revenues
                $revenues += intval($amount);
                // Add to costs
                $costs += intval($payed_by_scavengg);

            } else if($status == "declined") {
                $color = ''.$red.'';
                $status_select .= '<option value="approved">approved</option>';
                $status_select .= '<option value="declined" selected>declined</option>';
                $status_select .= '<option value="refunded">refunded</option>';
                $status_select .= '<option value="error">error</option>';
            } else if($status == "refunded") {
                $color = ''.$red.'';
                $status_select .= '<option value="approved">approved</option>';
                $status_select .= '<option value="declined">declined</option>';
                $status_select .= '<option value="refunded" selected>refunded</option>';
                $status_select .= '<option value="error">error</option>';
            } else {
                $color = ''.$red.'';
                $status_select .= '<option value="approved">approved</option>';
                $status_select .= '<option value="declined">declined</option>';
                $status_select .= '<option value="refunded">refunded</option>';
                $status_select .= '<option value="error" selected>error</option>';
            }

            //Get customer info
            $query_customer = mysqli_query($db_conx, "SELECT name FROM users WHERE id='$db_customer_id' LIMIT 1");
            $customer_rows = mysqli_fetch_row($query_customer);
            $customer_name = $customer_rows[0];

            $revenu_rows .= '<tr>';
            $revenu_rows .= '<td style="background:'.$color.';" onclick="flyout(\'view_transaction\', \''.$tran_id.'\')">'.$tran_id.'</td>';
            $revenu_rows .= '<td>'.$customer_name.' #'.$db_customer_id.'</td>';
            $revenu_rows .= '<td>'.$amount.'$</td>';
            $revenu_rows .= '<td>'.$bt_customer.'</td>';
            $revenu_rows .= '<td>'.$bt_transaction.'</td>';
            $revenu_rows .= '<td>'.date("M j H:i Y", strtotime($time)).'</td>';
            $revenu_rows .= '<td><select onchange="toggleStatus(\'transactions\', \'status\', \''.$tran_id.'\', this.value)">'.$status_select.'</select></td>';
            $revenu_rows .= '</tr>';

        }

    }
        // Total profits
        $profit = $revenues - $costs - $revenues*$bt_fee;
        if($profit < 0) {
            $color = ''.$red.'';
        } else {
            $color = ''.$green.'';
        }

        $revenu .= '<table class="table table-hover console_table">';
        $revenu .= '<tr>';
        $revenu .= '<th>Revenues</th>';
        $revenu .= '<th>Costs</th>';
        $revenu .= '<th>Profit</th>';
        $revenu .= '</tr>';
        $revenu .= '<tr>';
        $revenu .= '<td>'.$revenues.'$</td>';
        $revenu .= '<td>'.$costs.'$</td>';
        $revenu .= '<td style="color:'.$color.';">'.$profit.'$</td>';
        $revenu .= '</tr>';
        $revenu .= '</table><hr>';
        //Revenues        
        $revenu .= '<table class="table table-hover console_table">';
        $revenu .= '<tr>';
        $revenu .= '<th>#</th>'; // transaction row id
        $revenu .= '<th>Customer</th>';
        $revenu .= '<th>Amount</th>';
        $revenu .= '<th>Braintree Customer</th>';
        $revenu .= '<th>Braintree Transaction</th>';
        $revenu .= '<th>Time</th>';
        $revenu .= '<th>Status</th>';
        $revenu .= '</tr>';
        $revenu .= ''.$revenu_rows.'';
        $revenu .= '</table>';

    echo $revenu;
    exit();

}
?>
<?php
// GET SALES AND ORDERS
if(isset($_POST["action"]) && $_POST["action"] == "get_orders_sales") {

    $query = mysqli_query($db_conx, "SELECT * FROM carts ORDER BY added DESC");
    $numrows = mysqli_num_rows($query);
    if($numrows < 1) {
        $orders_rows .= '<tr><td>No orders yet.</td>';
        $orders_rows .= '<td></td>';
        $orders_rows .= '<td></td>';
        $orders_rows .= '<td></td>';
        $orders_rows .= '<td></td>';
        $orders_rows .= '<td></td>';
        $orders_rows .= '<td></td>';
        $orders_rows .= '<td></td>';
        $orders_rows .= '<td></td>';
        $orders_rows .= '<td></td>';
        $orders_rows .= '</tr>';

        $sales_rows .= '<tr><td>No sales yet.</td>';
        $sales_rows .= '<td></td>';
        $sales_rows .= '<td></td>';
        $sales_rows .= '<td></td>';
        $sales_rows .= '<td></td>';
        $sales_rows .= '<td></td>';
        $sales_rows .= '<td></td>';
        $sales_rows .= '<td></td>';
        $sales_rows .= '<td></td>';
        $sales_rows .= '<td></td>';
        $sales_rows .= '</tr>';
    } else {    

        while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
            $cart_row_id = $row["id"];
            $item_id = $row["item_id"];
            $customer = $row["customer"]; // DB customer id
            $size = $row["size"];
            $state = $row["state"];
            $status = $row["status"]; // pending, payed, delivered, error
            $purchased = $row["purchased"]; // timestamp of purchased time
            $db_transaction_id = $row["db_transaction_id"]; // id of row in transaction table if customer bought the item
            $added = $row["added"]; // timestamp when user added to item to their cart

            //Get item info
            $query_item = mysqli_query($db_conx, "SELECT caption, pic, brand FROM store WHERE id='$item_id' LIMIT 1");
            $rows = mysqli_fetch_row($query_item);
            $item_caption = $rows[0];
            $item_pic = $rows[1];
            $item_brand = $rows[2];

            //Get customer info
            $query_customer = mysqli_query($db_conx, "SELECT name, delivery FROM users WHERE id='$customer' LIMIT 1");
            $customer_rows = mysqli_fetch_row($query_customer);
            $customer_name = $customer_rows[0];
            $delivery = $customer_rows[1];

            $status_select = '';

            if($status == "pending") { //in the cart of the customer (helps predict demand)
                $status_select .= '<option value="pending" selected>pending</option>';
                $status_select .= '<option value="payed">payed</option>';
                $status_select .= '<option value="delivered">delivered</option>';
                $status_select .= '<option value="error">error</option>';
                $status_select .= '<option value="refunded">refunded</option>';

                $orders_rows .= '<tr>';
                $orders_rows .= '<td style="background:'.$yellow.';">'.$cart_row_id.'</td>';
                $orders_rows .= '<td><div class="dash_pic" style="background:url(https://scavengg.com/store/'.$item_pic.') no-repeat center;background-size:contain;"></div></td>';
                $orders_rows .= '<td>'.$item_caption.' #'.$item_id.'</td>';
                $orders_rows .= '<td>'.$size.'</td>';
                $orders_rows .= '<td>'.$state.'</td>';
                $orders_rows .= '<td>'.$customer_name.' #'.$customer.'</td>';
                $orders_rows .= '<td>'.$delivery.'</td>';
                $orders_rows .= '<td>'.date("M j H:i A", strtotime($added)).'</td>';
                $orders_rows .= '<td><select onchange="toggleStatus(\'carts\', \'status\', \''.$cart_row_id.'\', this.value)">'.$status_select.'</select></td>';
                $orders_rows .= '</tr>';

            } else if($status == "payed") { //ORDER

                $item_status_switch = '<label class="switch"><input type="checkbox" onclick="switchToggle(this, \'carts\', \'status\', \''.$cart_row_id.'\', \''.$customer.'\')"><span class="slider round"></span></label>';

                $status_select .= '<option value="pending">pending</option>';
                $status_select .= '<option value="payed" selected>payed</option>';
                $status_select .= '<option value="delivered">delivered</option>';
                $status_select .= '<option value="error">error</option>';
                $status_select .= '<option value="refunded">refunded</option>';

                $orders_rows .= '<tr>';
                $orders_rows .= '<td style="background:'.$green.';">'.$cart_row_id.'</td>';
                $orders_rows .= '<td><div class="dash_pic" style="background:url(https://scavengg.com/store/'.$item_pic.') no-repeat center;background-size:contain;"></div></td>';
                $orders_rows .= '<td>'.$item_caption.' #'.$item_id.'</td>';
                $orders_rows .= '<td>'.$size.'</td>';
                $orders_rows .= '<td>'.$state.'</td>';
                $orders_rows .= '<td>'.$customer_name.' #'.$customer.'</td>';
                $orders_rows .= '<td>'.$delivery.'</td>';
                $orders_rows .= '<td>'.date("M j H:i A", strtotime($added)).'</td>';
                //$orders_rows .= '<td>'.$item_status_switch.'</td>';
                $orders_rows .= '<td><select onchange="toggleStatus(\'carts\', \'status\', \''.$cart_row_id.'\', this.value)">'.$status_select.'</select></td>';
                $orders_rows .= '</tr>';

            } else if($status == "delivered") { //SALES

                $item_status_switch = '<label class="switch"><input type="checkbox" onclick="switchToggle(this, \'carts\', \'status\', \''.$cart_row_id.'\', \''.$customer.'\')" checked><span class="slider round"></span></label>';

                $status_select .= '<option value="pending">pending</option>';
                $status_select .= '<option value="payed">payed</option>';
                $status_select .= '<option value="delivered" selected>delivered</option>';
                $status_select .= '<option value="error">error</option>';
                $status_select .= '<option value="refunded">refunded</option>';

                $sales_rows .= '<tr>';
                //$sales_rows .= '<td style="background:'.$blue.';">'.$cart_row_id.'</td>';
                $sales_rows .= '<td>'.$cart_row_id.'</td>';
                $sales_rows .= '<td><div class="dash_pic" style="background:url(https://scavengg.com/store/'.$item_pic.') no-repeat center;background-size:contain;"></div></td>';
                $sales_rows .= '<td>'.$item_caption.' #'.$item_id.'</td>';
                $sales_rows .= '<td>'.$size.'</td>';
                $sales_rows .= '<td>'.$state.'</td>';
                $sales_rows .= '<td>'.$customer_name.' #'.$customer.'</td>';
                $sales_rows .= '<td>'.$delivery.'</td>';
                $sales_rows .= '<td>#'.$db_transaction_id.'</td>';
                $sales_rows .= '<td>'.date("M j H:i A", strtotime($purchased)).'</td>';
                //$sales_rows .= '<td>'.$item_status_switch.'</td>';
                $sales_rows .= '<td><select onchange="toggleStatus(\'carts\', \'status\', \''.$cart_row_id.'\', this.value)">'.$status_select.'</select></td>';
                $sales_rows .= '</tr>';
                
            } else if($status == 'refunded') {

                $status_select .= '<option value="pending">pending</option>';
                $status_select .= '<option value="payed">payed</option>';
                $status_select .= '<option value="delivered">delivered</option>';
                $status_select .= '<option value="error">error</option>';
                $status_select .= '<option value="refunded" selected>refunded</option>';

                $orders_rows .= '<tr>';
                $orders_rows .= '<td style="background:'.$red.';">'.$cart_row_id.'</td>';
                $orders_rows .= '<td><div class="dash_pic" style="background:url(https://scavengg.com/store/'.$item_pic.') no-repeat center;background-size:contain;"></div></td>';
                $orders_rows .= '<td>'.$item_caption.' #'.$item_id.'</td>';
                $orders_rows .= '<td>'.$size.'</td>';
                $orders_rows .= '<td>'.$state.'</td>';
                $orders_rows .= '<td>'.$customer_name.' #'.$customer.'</td>';
                $orders_rows .= '<td>'.$delivery.'</td>';
                $orders_rows .= '<td>'.date("M j H:i A", strtotime($added)).'</td>';
                //$orders_rows .= '<td style="color:'.$red.';">Refunded</td>';
                $orders_rows .= '<td><select onchange="toggleStatus(\'carts\', \'status\', \''.$cart_row_id.'\', this.value)">'.$status_select.'</select></td>';
                $orders_rows .= '</tr>';
            } else { // status = 'error'

                $status_select .= '<option value="pending">pending</option>';
                $status_select .= '<option value="payed">payed</option>';
                $status_select .= '<option value="delivered">delivered</option>';
                $status_select .= '<option value="error" selected>error</option>';
                $status_select .= '<option value="refunded">refunded</option>';

                $orders_rows .= '<tr>';
                $orders_rows .= '<td style="background:'.$red.';">'.$cart_row_id.'</td>';
                $orders_rows .= '<td><div class="dash_pic" style="background:url(https://scavengg.com/store/'.$item_pic.') no-repeat center;background-size:contain;"></div></td>';
                $orders_rows .= '<td>'.$item_caption.' #'.$item_id.'</td>';
                $orders_rows .= '<td>'.$size.'</td>';
                $orders_rows .= '<td>'.$state.'</td>';
                $orders_rows .= '<td>'.$customer_name.' #'.$customer.'</td>';
                $orders_rows .= '<td>'.$delivery.'</td>';
                $orders_rows .= '<td>'.date("M j H:i A", strtotime($added)).'</td>';
                //$orders_rows .= '<td style="color:'.$red.';">error</td>';
                $orders_rows .= '<td><select onchange="toggleStatus(\'carts\', \'status\', \''.$cart_row_id.'\', this.value)">'.$status_select.'</select></td>';
                $orders_rows .= '</tr>';
            }

        }

    }

        if($sales_rows == "") {
            $sales_rows .= '<tr><td>No sales yet.</td>';
            $sales_rows .= '<td></td>';
            $sales_rows .= '<td></td>';
            $sales_rows .= '<td></td>';
            $sales_rows .= '<td></td>';
            $sales_rows .= '<td></td>';
            $sales_rows .= '<td></td>';
            $sales_rows .= '<td></td>';
            $sales_rows .= '<td></td>';
            $sales_rows .= '<td></td>';
            $sales_rows .= '</tr>';
        }

        if($orders_rows == "") {
            $orders_rows .= '<tr><td>No orders yet.</td>';
            $orders_rows .= '<td></td>';
            $orders_rows .= '<td></td>';
            $orders_rows .= '<td></td>';
            $orders_rows .= '<td></td>';
            $orders_rows .= '<td></td>';
            $orders_rows .= '<td></td>';
            $orders_rows .= '<td></td>';
            $orders_rows .= '<td></td>';
            $orders_rows .= '</tr>';
        }

        //Orders
        $orders .= '<table class="table table-hover console_table">';
        $orders .= '<tr>';
        $orders .= '<th>#</th>'; // cart row id
        $orders .= '<th>Item</th>';
        $orders .= '<th>Description</th>';
        $orders .= '<th>Size</th>';
        $orders .= '<th>Condition</th>';
        $orders .= '<th>Customer</th>';
        $orders .= '<th>Delivery</th>';
        $orders .= '<th>Added</th>';
        $orders .= '<th>Status</th>';
        $orders .= '</tr>';
        $orders .= ''.$orders_rows.'';
        $orders .= '</table>';

        //Sales
        $sales .= '<table class="table table-hover console_table">';
        $sales .= '<tr>';
        $sales .= '<th>#</th>'; // cart row id
        $sales .= '<th>Item</th>';
        $sales .= '<th>Description</th>';
        $sales .= '<th>Size</th>';
        $sales .= '<th>Condition</th>';
        $sales .= '<th>Customer</th>';
        $sales .= '<th>Delivery</th>';
        $sales .= '<th>Transaction ID</th>';
        $sales .= '<th>Purchased</th>';
        $sales .= '<th>Status</th>';
        $sales .= '</tr>';
        $sales .= ''.$sales_rows.'';
        $sales .= '</table>';

    echo ''.$orders.'|SPLIT|'.$sales.'';
    exit();

}
?>
<?php
// UPDATE ITEM
if(isset($_POST["action"]) && $_POST["action"] == "update_item") {

    $item_id = preg_replace('#[^0-9]#', '', $_POST['item_id']);

    $caption = htmlentities($_POST['caption']);
	$caption = mysqli_real_escape_string($db_conx, $caption);
    $brand = htmlentities($_POST['brand']);
	$brand = mysqli_real_escape_string($db_conx, $brand);
    $sizes = htmlentities($_POST['sizes']);
	$sizes = mysqli_real_escape_string($db_conx, $sizes);
    $category = htmlentities($_POST['category']);
	$category = mysqli_real_escape_string($db_conx, $category);
    //$price_new = preg_replace('#[^0-9]#', '', $_POST["price_new"]);
    //$price_used = preg_replace('#[^0-9]#', '', $_POST["price_used"]);
    $season = htmlentities($_POST['season']);
	$season = mysqli_real_escape_string($db_conx, $season);

    /*if($price_new != "" && $price_used != "") {
        $min_newish = round(($price_new - $price_new*0.05) / 10) * 10; // -5%, round to nearest 10
    }*/

    $sql = "UPDATE store SET caption='$caption', category='$category', sizes='$sizes', brand='$brand', season='$season', added=now() 
            WHERE id='$item_id' LIMIT 1";
    $query = mysqli_query($db_conx, $sql);

    echo 'item_updated';
    exit();

}
?>
<?php
// DELETE ITEM
if(isset($_POST["action"]) && $_POST["action"] == "delete_item") {

    $item_id = preg_replace('#[^0-9]#', '', $_POST['item_id']);

    $query = mysqli_query($db_conx, "SELECT pic FROM store WHERE id='$item_id' LIMIT 1");
    $row = mysqli_fetch_row($query);
    $pic = $row[0];

    //Delete the image for that item
    unlink('/var/www/store/'.$pic.'');

    mysqli_query($db_conx, "DELETE FROM store WHERE id='$item_id' LIMIT 1");
    
    //REMOVE item from 'carts' and 'searches' table
    mysqli_query($db_conx, "DELETE FROM carts WHERE item_id='$item_id' LIMIT 1");
    mysqli_query($db_conx, "DELETE FROM searches WHERE item_id='$item_id' LIMIT 1");

    echo 'item_deleted';
    exit();

}
?>
<?php
// STRINGIFY ITEM SIZES
if(isset($_POST["action"]) && $_POST["action"] == "stringify_sizes") {

    $size_id = preg_replace('#[^0-9]#', '', $_POST['size_id']);

        // Get the 'stringified' sizes 
        $query_sizes = mysqli_query($db_conx_test, "SELECT size FROM charts WHERE id='$size_id' LIMIT 1");
        $sizes_row = mysqli_fetch_row($query_sizes);
        $string_sizes = $sizes_row[0];

        echo $string_sizes;
        exit();
}
?>
<?php
// UPDATE/SAVE PRICE SIZE
if(isset($_POST["action"]) && $_POST["action"] == "save_price_size") {

    $price = preg_replace('#[^0-9]#', '', $_POST['price']);
    $size = $_POST['size'];
    $item_id = preg_replace('#[^0-9]#', '', $_POST['item_id']);

    if($price != "" || $size != "" || $item_id != "") {
        mysqli_query($db_conx_test, "UPDATE prices SET price='$price' WHERE item_id='$item_id' AND size='$size' LIMIT 1");
        echo 'price_size_saved';
        exit();
    } else {
        echo 'error_saving_price_for_size';
        exit();
    }

}
?>
<?php
// PRICES SIZES TABLE
if(isset($_POST["action"]) && $_POST["action"] == "prices_sizes_table") {

    $item_id = preg_replace('#[^0-9]#', '', $_POST['item_id']);

            // Construct price per size table
            $query_prices_sizes = mysqli_query($db_conx_test, "SELECT * FROM prices WHERE item_id='$item_id'");
            $sizes_table .= '<table class="table table-hover console_table">';
            while($row_prices = mysqli_fetch_array($query_prices_sizes, MYSQLI_ASSOC)) {
                $size = $row_prices["size"];
                $price = $row_prices["price"];
    
                $sizes_table .= '<tr>';
                $sizes_table .= '<th>'.$size.'</th>';
                $sizes_table .= '<th>&nbsp;</th>';
                $sizes_table .= '</tr>';
                $sizes_table .= '<tr>';
                $sizes_table .= '<td><input id="price_size_'.$item_id.'_'.$size.'" class="dash_inputs" placeholder="Price for size '.$size.'" value="'.$price.'" onkeypress="return event.charCode >= 48 && event.charCode <= 57" autocomplete="off" maxlength="10"></td>';
                $sizes_table .= '<td><button class="dash_btns" style="padding:5px;margin:10px 0px;" onclick="savePriceSize(\'price_size_'.$item_id.'_'.$size.'\', \''.$item_id.'\', \''.$size.'\')">Save</button></td>';
                $sizes_table .= '</tr>';
            }   
            $sizes_table .= '</table>';

            echo $sizes_table;
            exit();
}
?>
<?php
// ITEMS DETAILS
if(isset($_POST["action"]) && $_POST["action"] == "get_item_details") {

    $item_id = preg_replace('#[^0-9]#', '', $_POST['item_id']);

    $query = mysqli_query($db_conx_test, "SELECT * FROM store WHERE id='$item_id' LIMIT 1");

        while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
            $caption = $row["caption"];
            $pic = $row["pic"];
            $category = $row["category"];
            $brand = $row["brand"];
            $sizes = $row["sizes"];
            $season = $row["season"];
            $price_new = $row["min_new"];
            $price_used = $row["min_used"];

        }

        // Get the 'stringified' sizes 
        $query_sizes = mysqli_query($db_conx_test, "SELECT size FROM charts WHERE id='$sizes' LIMIT 1");
        $sizes_row = mysqli_fetch_row($query_sizes);
        $string_sizes = $sizes_row[0];
        
        $item .= '<form id="update_settings_image" style="border-radius:2px;" action="" method="post" enctype="multipart/form-data">';
        $item .= '<div id="update_avatar_preview" style="border-radius:2px;">';
        $item .= '<label for="update_file_settings" id="update_file_label_settings">';
        $item .= '<img id="update_file_selector_settings" class="update_settings_previewing" src="https://scavengg.com/store/'.$pic.'">';
        $item .= '<input type="file" name="update_file_settings" id="update_file_settings" required />';
        $item .= '</label>';
        $item .= '</div>';
        $item .= '</form>';

        $item .= '<input id="update_caption" class="dash_inputs" placeholder="Description" value="'.$caption.'" autocomplete="off" maxlength="100">';
        $item .= '<select id="update_subcategories" class="dash_inputs" value="'.$category.'"></select>';
        $item .= '<input id="update_brand" class="dash_inputs" value="'.$brand.'" placeholder="Brand Name" autocomplete="off">';
        $item .= '<select id="update_size_chart" class="dash_inputs_half" value="'.$sizes.'"></select>'; //onchange="changedItemSize(this.value, \'price_per_size\')"
        $item .= '<input id="update_season" class="dash_inputs_half" placeholder="Season" value="'.$season.'" autocomplete="off"><br>';
        
        //$item .= '<input id="update_price_new" class="dash_inputs_half" value="'.$price_new.'" placeholder="Price New" onkeypress="return event.charCode >= 48 && event.charCode <= 57" autocomplete="off" maxlength="10">';
        //$item .= '<input id="update_price_used" class="dash_inputs_half" value="'.$price_used.'" placeholder="Price Used" onkeypress="return event.charCode >= 48 && event.charCode <= 57" autocomplete="off" maxlength="10">';
        
        $item .= '<div class="small_mess">* Taxes will be added at checkout</div>';
        $item .= '<button id="update_item_btn" class="dash_btns" onclick="updateItem(\''.$item_id.'\')">Update</button>';

        //Price per size
        $item .= '<hr><div id="price_per_size"></div>';

        $item .= '<hr><button class="red_dash_btns" style="float:right;" onclick="deleteItem(\''.$item_id.'\')">Delete</button>';


        echo ''.$item.'|'.$category.'|'.$sizes.'|'.$string_sizes.'';
        exit();

}
?>
<?php
// USER CART
if(isset($_POST["action"]) && $_POST["action"] == "get_user_cart") {

    $uid = preg_replace('#[^0-9]#', '', $_POST['uid']);

    $query = mysqli_query($db_conx, "SELECT * FROM carts WHERE customer='$uid' ORDER BY added DESC");
    $numrows = mysqli_num_rows($query);
    if($numrows < 1) { // user has empty cart
        $cart_rows .= '<tr><td>Cart empty</td>';
        $cart_rows .= '<td></td>';
        $cart_rows .= '<td></td>';
        $cart_rows .= '<td></td>';
        $cart_rows .= '<td></td>';
        $cart_rows .= '<td></td>';
        $cart_rows .= '<td></td>';
        $cart_rows .= '<td></td>';
        $cart_rows .= '<td></td>';
        $cart_rows .= '<td></td>';
        $cart_rows .= '</tr>';
    } else {

        while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
            $cart_id = $row["id"];
            $item_id = $row["item_id"];
            $size = $row["size"];
            $condition = $row["state"];
            $purchased = $row["purchased"];
            $status = $row["status"];
            $added = $row["added"];

            if($purchased == "0000-00-00 00:00:00") {
                $purchased_time = 'not yet';
            } else {
                $purchased_time = date("M j H:i A", strtotime($purchased));
            }

            //Get item info
            $query_item = mysqli_query($db_conx, "SELECT caption, pic, brand FROM store WHERE id='$item_id' LIMIT 1");
            $rows = mysqli_fetch_row($query_item);
            $item_caption = $rows[0];
            $item_pic = $rows[1];
            $item_brand = $rows[2];

            if($status == "pending") {
                $color = 'style="background:'.$yellow.';"';
            } else if($status == "payed") {
                $color = 'style="background:'.$green.';"';
            } else if($status == "delivered") {
                $color = 'style="background:'.$blue.';"';
            } else {
                $color = 'style="background:'.$red.';"';
            }

            $cart_rows .= '<tr>';
            $cart_rows .= '<td '.$color.'>'.$cart_id.'</td>';
            $cart_rows .= '<td><div class="dash_pic" style="background:url(https://scavengg.com/store/'.$item_pic.') no-repeat center;background-size:contain;"></div></td>';
            $cart_rows .= '<td>'.$item_caption.' #'.$item_id.'</td>';
            $cart_rows .= '<td>'.$item_brand.'</td>';
            $cart_rows .= '<td>'.$size.'</td>';
            $cart_rows .= '<td>'.$condition.'</td>';
            $cart_rows .= '<td>'.$status.'</td>';
            $cart_rows .= '<td>'.$purchased_time.'</td>';
            $cart_rows .= '<td>'.date("M j H:i A", strtotime($added)).'</td>';
            $cart_rows .= '</tr>';

        }
    }

            //Get customer info
            $query_customer = mysqli_query($db_conx, "SELECT email, phone, name, delivery FROM users WHERE id='$uid' LIMIT 1");
            $customer_rows = mysqli_fetch_row($query_customer);
            $customer_email = $customer_rows[0];
            $customer_phone = $customer_rows[1];
            $customer_name = $customer_rows[2];
            $delivery = $customer_rows[3];

        $cart .= '<div>'.$customer_name.'</div>';
        if($customer_phone) {
            $cart .= '<input onkeyup="savePhone(this.value, \'\', \''.$uid.'\')" class="dash_inputs" placeholder="Enter phone" value="'.$customer_phone.'" maxlength="10">';
        } else {
            $cart .= '<input onkeyup="savePhone(this.value, \'\', \''.$uid.'\')" class="dash_inputs" placeholder="Enter phone" maxlength="10">';
        }
        if($customer_phone || $customer_email) {
            $cart .= '<input id="user_notify_input" class="dash_inputs" placeholder="Enter message">';
            if($customer_phone) {
                $cart .= '<button class="dash_btns" onclick="userNotify(\'sms\', \'user_notify_input\', \''.$customer_phone.'\')">Send SMS</button>';
            }
            if($customer_email != "undefined") {
                $cart .= '<button class="dash_btns" onclick="userNotify(\'email\', \'user_notify_input\', \''.$customer_email.'\')">Send Email</button>';
            }
        }
        $cart .= '<table class="table table-hover console_table">';
        $cart .= '<tr>';
        $cart .= '<th>#</th>';
        $cart .= '<th>Item</th>';
        $cart .= '<th>Description</th>';
        $cart .= '<th>Brand</th>';
        $cart .= '<th>Size</th>';
        $cart .= '<th>Condition</th>';
        $cart .= '<th>Status</th>';
        $cart .= '<th>Purchased</th>';
        $cart .= '<th>Added</th>';
        $cart .= '</tr>';
        $cart .= ''.$cart_rows.'';
        $cart .= '</table>';

        echo $cart;
        exit();

}
?>
<?php
// TOGGLE SWITCH
if(isset($_POST["action"]) && $_POST["action"] == "toggle_switch") {

    $table = htmlentities($_POST['table']);
	$table = mysqli_real_escape_string($db_conx, $table);
    $col = htmlentities($_POST['col']);
	$col = mysqli_real_escape_string($db_conx, $col);
    $id = preg_replace('#[^0-9]#', '', $_POST['id']);
    $value = preg_replace('#[^a-z0-9]#i', '', $_POST['value']);

    mysqli_query($db_conx, "UPDATE $table SET $col='$value' WHERE id='$id' LIMIT 1");


    echo 'toggled_success';
    exit();
}
?>
<?php
// GET FULL STORE
if(isset($_POST["action"]) && $_POST["action"] == "get_store") {

    $query = mysqli_query($db_conx, "SELECT * FROM store ORDER BY brand ASC");
    $numrows = mysqli_num_rows($query);
    if($numrows < 1) {
        $store_rows .= '<tr><td>The store is empty.</td>';
        $store_rows .= '<td></td>';
        $store_rows .= '<td></td>';
        $store_rows .= '<td></td>';
        $store_rows .= '<td></td>';
        $store_rows .= '<td></td>';
        $store_rows .= '<td></td>';
        $store_rows .= '<td></td>';
        $store_rows .= '<td></td>';
        $store_rows .= '</tr>';
    } else {    

        while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
            $item_id = $row["id"];
            $caption = $row["caption"];
            $pic = $row["pic"];
            $category_id = $row["category"];
            $sizes_id = $row["sizes"];
            $min_new = $row["min_new"];
            $min_used = $row["min_used"];
            $min_newish = $row["min_newish"];
            $brand = $row["brand"];
            $status = $row["active"];

            //Category name
            $query1 = mysqli_query($db_conx, "SELECT category FROM subcategories WHERE id='$category_id' LIMIT 1");
            $catego = mysqli_fetch_row($query1);
            $category = $catego[0];

            //Size chart
            $query2 = mysqli_query($db_conx, "SELECT size FROM charts WHERE id='$sizes_id' LIMIT 1");
            $size_row = mysqli_fetch_row($query2);
            $sizes = $size_row[0];

            if($status == 1) {
                $active = '<label class="switch"><input type="checkbox" onclick="switchToggle(this, \'store\', \'active\', \''.$item_id.'\')" checked><span class="slider round"></span></label>';
            } else {
                $active = '<label class="switch"><input type="checkbox" onclick="switchToggle(this, \'store\', \'active\', \''.$item_id.'\')"><span class="slider round"></span></label>';
            }

            $store_rows .= '<tr id="search_store_'.$item_id.'" class="search_store">';
            $store_rows .= '<td>'.$item_id.'</td>';
            $store_rows .= '<td onclick="flyout(\'view_item_details\', \''.$item_id.'\')"><div class="dash_pic" style="background:url(https://scavengg.com/store/'.$pic.') no-repeat center;background-size:contain;"></div></td>';
            $store_rows .= '<td id="item_caption_'.$item_id.'">'.$caption.'</td>';
            $store_rows .= '<td>'.$brand.'</td>';
            $store_rows .= '<td>'.$category.'</td>';
            $store_rows .= '<td>'.$sizes.'</td>';
            $store_rows .= '<td>'.$min_new.'</td>';
            $store_rows .= '<td>'.$min_used.'</td>';
            $store_rows .= '<td>'.$active.'</td>';
            $store_rows .= '</tr>';

        }

    }
    
        $store .= '<table class="table table-hover console_table">';
        $store .= '<tr>';
        $store .= '<th>#</th>';
        $store .= '<th>Item</th>';
        $store .= '<th>Description</th>';
        $store .= '<th>Brand</th>';
        $store .= '<th>Category</th>';
        $store .= '<th>Size</th>';
        $store .= '<th>New</th>';
        $store .= '<th>Used</th>';
        $store .= '<th>Available</th>';
        $store .= '</tr>';
        $store .= ''.$store_rows.'';
        $store .= '</table>';

    echo ''.$store.'';
    exit();

}
?>
<?php
// GET ALL USERS
if(isset($_POST["action"]) && $_POST["action"] == "get_users") {

    $query = mysqli_query($db_conx, "SELECT * FROM users ORDER BY created DESC");
    $numrows = mysqli_num_rows($query);
    if($numrows < 1) {
        $user_rows .= '<tr><td>No users in database.</td>';
        $user_rows .= '<td></td>';
        $user_rows .= '<td></td>';
        $user_rows .= '<td></td>';
        $user_rows .= '<td></td>';
        $user_rows .= '<td></td>';
        $user_rows .= '<td></td>';
        $user_rows .= '<td></td>';
        $user_rows .= '</tr>';
    } else {    

        while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
            $uid = $row["id"];
            $name = $row["name"];
            $email = $row["email"];
            $phone = $row["phone"];
            $bt_id = $row["bt_id"];
            $gender = $row["gender"];
            $age_range = $row["age_range"];
            $location = $row["location"];
            $lastlogin = $row["lastlogin"];
            $created = $row["created"];
            $status = $row["activated"];

            $created_time = date("M j", strtotime($created));
            $time_ago = time_elapsed_string($lastlogin);
            if($time_ago == 'just now') {
                $active_ago = $time_ago;
            } else {
                $active_ago = ''.$time_ago.'';
            }

            if($bt_id == "" || $bt_id == 0) {
                $hasBraintree = '<span style="color:'.$red.'">no</span>';
            } else {
                $hasBraintree = '<span style="color:'.$green.'">yes</span>';
            }

            if($status == 1) {
                $active = '<label class="switch"><input type="checkbox" onclick="switchToggle(this, \'users\', \'activated\', \''.$uid.'\')" checked><span class="slider round"></span></label>';
            } else {
                $active = '<label class="switch"><input type="checkbox" onclick="switchToggle(this, \'users\', \'activated\', \''.$uid.'\')"><span class="slider round"></span></label>';
            }

            $user_rows .= '<tr>';
            $user_rows .= '<td>'.$uid.'</td>';
            $user_rows .= '<td onclick="flyout(\'view_user_cart\', \''.$uid.'\')">'.$name.'</td>';
            $user_rows .= '<td>'.$phone.'</td>';
            $user_rows .= '<td>'.$email.'</td>';
            $user_rows .= '<td>'.$bt_id.'</td>';
            //$user_rows .= '<td>'.$gender.'</td>';
            //$user_rows .= '<td>'.$age_range.'</td>';
            //$user_rows .= '<td>'.$location.'</td>';
            //$user_rows .= '<td>'.$hasBraintree.'</td>';
            $user_rows .= '<td>'.$active_ago.'</td>';
            $user_rows .= '<td>'.$created_time.'</td>';
            $user_rows .= '<td>'.$active.'</td>';
            $user_rows .= '</tr>';

        }

    }

        $users .= '<table class="table table-hover console_table">';
        $users .= '<tr>';
        $users .= '<th>#</th>';
        $users .= '<th>Name</th>';
        $users .= '<th>Phone</th>';
        $users .= '<th>Email</th>';
        $users .= '<th>Braintree ID</th>';
        //$users .= '<th>Gender</th>';
        //$users .= '<th>Age range</th>';
        //$users .= '<th>Location</th>';
        //$users .= '<th>Payment</th>';
        $users .= '<th>Active</th>';
        $users .= '<th>Created</th>';
        $users .= '<th>Activated</th>';
        $users .= '</tr>';
        $users .= ''.$user_rows.'';
        $users .= '</table>';

    echo ''.$users.'';
    exit();

}
?>
<?php
// GET ALL SUBCATEGORIES
if(isset($_POST["action"]) && $_POST["action"] == "get_all_subcat") {
        //Get according sizes
        $query = mysqli_query($db_conx, "SELECT * FROM subcategories WHERE active='1'");
        $numrow = mysqli_num_rows($query);
        if($numrow < 1) {
            $subcat .= '<option></option>';
        } else {
            while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
                $subcat_id = $row["id"];
                $parent_cat_id = $row["parent"];
                $subcategory = $row["category"];

                //Get Parent category name
                $query_cat = mysqli_query($db_conx, "SELECT category FROM categories WHERE id='$parent_cat_id' LIMIT 1");
                $row_parent = mysqli_fetch_row($query_cat);
                $parent_cat = $row_parent[0];

                $subcat .= '<option value="'.$subcat_id.'">'.$subcategory.'</option>';

                $subcat_rows .= '<tr>';
                $subcat_rows .= '<td>'.$subcat_id.'</td>';
                $subcat_rows .= '<td>'.$subcategory.'</td>';
                $subcat_rows .= '<td>'.$parent_cat.'</td>';
                $subcat_rows .= '</tr>';

            }
        }

        $subcat_table .= '<table class="table table-hover console_table">';
        $subcat_table .= '<tr>';
        $subcat_table .= '<th>#</th>';
        $subcat_table .= '<th>Subcategory</th>';
        $subcat_table .= '<th>Category</th>';
        $subcat_table .= '</tr>';
        $subcat_table .= ''.$subcat_rows.'';
        $subcat_table .= '</table>';

        $subcategories = '<option selected="true" disabled="true" value="">Select Category</option>'.$subcat.'';
        echo ''.$subcategories.'|'.$subcat_table.'';
        exit();
}
?>
<?php
// GET ALL SIZES POSSIBLE
if(isset($_POST["action"]) && $_POST["action"] == "get_all_sizes") {
        //Get according sizes
        $query = mysqli_query($db_conx, "SELECT * FROM charts WHERE active='1'");
        $numrow = mysqli_num_rows($query);
        if($numrow < 1) {
            $sizes .= '<option></option>';
        } else {
            while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
                $size_id = $row["id"];
                $size = $row["size"];

                $sizes_rows .= '<tr>';
                $sizes_rows .= '<td>'.$size_id.'</td>';
                $sizes_rows .= '<td>'.$size.'</td>';
                $sizes_rows .= '</tr>';

                $sizes .= '<option value="'.$size_id.'">'.$size.'</option>';
            }
        }

        $sizes_table .= '<table class="table table-hover console_table">';
        $sizes_table .= '<tr>';
        $sizes_table .= '<th>#</th>';
        $sizes_table .= '<th>Size</th>';
        $sizes_table .= '</tr>';
        $sizes_table .= ''.$sizes_rows.'';
        $sizes_table .= '</table>';


        $size_chart = '<option selected="true" disabled="true" value="">Size Chart</option>'.$sizes.'';
        echo ''.$size_chart.'|'.$sizes_table.'';
        exit();
}
?>
<?php
// DASHBOARD UPLOAD
if(isset($_POST["action"]) && $_POST["action"] == "upload_item") {

    $caption = htmlentities($_POST['caption']);
	$caption = mysqli_real_escape_string($db_conx_test, $caption);
    $brand = htmlentities($_POST['brand']);
	$brand = mysqli_real_escape_string($db_conx_test, $brand);
    $sizes = htmlentities($_POST['sizes']);
	$sizes = mysqli_real_escape_string($db_conx_test, $sizes);
    $category = htmlentities($_POST['category']);
	$category = mysqli_real_escape_string($db_conx_test, $category);
    $price_new = preg_replace('#[^0-9]#', '', $_POST["price_new"]);
    //$price_used = preg_replace('#[^0-9]#', '', $_POST["price_used"]);
    $season = htmlentities($_POST['season']);
	$season = mysqli_real_escape_string($db_conx_test, $season);

    $sql = "INSERT INTO store (caption, category, sizes, min_new, brand, season, added) 
            VALUES ('$caption','$category','$sizes','$price_new','$brand','$season',now())";
    $query = mysqli_query($db_conx_test, $sql);
    $item_id = mysqli_insert_id($db_conx_test);

    // Get the 'stringified' sizes 
    $query_sizes = mysqli_query($db_conx_test, "SELECT size FROM charts WHERE id='$sizes' LIMIT 1");
    $sizes_row = mysqli_fetch_row($query_sizes);
    $string_sizes = $sizes_row[0];

    $sizes_array = explode("-", $string_sizes);
    for($i=0; $i < count($sizes_array); $i++) {
        $insert_rows .= '('.$item_id.', "'.$sizes_array[$i].'", '.$price_new.', "1", now())';
        if($i < (count($sizes_array) - 1)) {
            $insert_rows .= ', ';
        }
    }
    $sql_prices = "INSERT INTO prices (item_id, size, price, active, added) VALUES $insert_rows";
    $query = mysqli_query($db_conx_test, $sql_prices);
    $price_size_id = mysqli_insert_id($db_conx_test);

    echo 'upload_success';
    exit();

}

?>