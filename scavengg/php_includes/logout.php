<?php 
include_once("db_conx.php");
?>
<?php
if(isset($_POST["action"]) && $_POST["action"] == "logout"){

session_start();
// Set Session data to an empty array
$_SESSION = array();
// Expire their cookie files
if(isset($_COOKIE["id"])) {
	setcookie("id", '', strtotime( '-30 days' ), '/');
}

// Destroy the session variables
session_destroy();
// Double check to see if their sessions exists
if(isset($_SESSION['userid'])){
	header("location: index.html");
} else {

    echo 'logged_out';
	exit();
} 

}

?>