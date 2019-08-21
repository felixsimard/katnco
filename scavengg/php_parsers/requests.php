<?php 
include_once("../php_includes/db_conx.php");
include_once("../php_includes/session.php");
include_once("../class/time_ago_class.php");
include_once("../class/phone_format.php");
include_once("../class/number_format.php");
?>
<?php
if(isset($_POST["action"]) && $_POST["action"] == "request_item") {

    $caption = htmlentities($_POST['caption']);
	$caption = mysqli_real_escape_string($db_conx, $caption);
    $size = htmlentities($_POST['size']);
	$size = mysqli_real_escape_string($db_conx, $size);
    $brand = htmlentities($_POST['brand']);
	$brand = mysqli_real_escape_string($db_conx, $brand);
    $season = htmlentities($_POST['season']);
	$season = mysqli_real_escape_string($db_conx, $season);

    if($log_id == "") {
        echo 'user_not_logged_id';
        exit();
    }

    $sql = "INSERT INTO requests (uid, description, size, status, added) 
            VALUES ('$log_id','$caption','$size','pending',now())";

    if($brand != "") {
        $sql = "INSERT INTO requests (uid, description, size, brand, status, added) 
                VALUES ('$log_id','$caption','$size','$brand','pending',now())";
    }
    if($season != "") {
        $sql = "INSERT INTO requests (uid, description, size, brand, season, status, added) 
                VALUES ('$log_id','$caption','$size','$brand','$season','pending',now())";
    }
    $query = mysqli_query($db_conx, $sql);
    $rid = mysqli_insert_id($db_conx);

    //Get user info
    $query_user = mysqli_query($db_conx, "SELECT email, phone, name FROM users WHERE id='$log_id' LIMIT 1");
    $rows_user = mysqli_fetch_row($query_user);
    $email = $rows_user[0];
    $phone = $rows_user[1];
    $name = html_entity_decode($rows_user[2]);

    // Notify admin
    $msg = 'New request on Scavengg on '.date("F d Y H:ia").'.';
    notify("felixsimard@gmail.com", $msg);
    notify("lasrywinc@yahoo.com", $msg);

    echo 'request_success|'.$phone.'|'.$name.'';
    exit();


}
?>