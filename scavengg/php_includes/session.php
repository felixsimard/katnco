<?php
session_start();
include_once("db_conx.php");
$log_id = "";

    $uid = preg_replace('#[^0-9]#', '', $_POST["uid"]);

    if($uid != "") {
        $_SESSION['userid'] = $uid;
        setcookie("id", $db_id, strtotime( '+30 days' ), "/", "", "", TRUE);

        //Set the log_id variable
        $log_id = $_SESSION['userid'];
    }

    //Set the log_id variable
    $log_id = $_SESSION['userid'];

    $ip = preg_replace('#[^0-9.]#', '', getenv('REMOTE_ADDR'));
	// UPDATE THEIR "IP" AND "LASTLOGIN" FIELDS
	$sql = "UPDATE users SET ip='$ip', lastlogin=now() WHERE id='$log_id' LIMIT 1";
    $query = mysqli_query($db_conx, $sql);


?>