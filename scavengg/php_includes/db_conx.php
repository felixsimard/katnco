<?php 
/*
$db_conx = mysqli_connect("localhost", "root", "HockeyLife12", "Proxy");
// Evaluate the connection
if (mysqli_connect_errno()) {
	echo mysqli_connect_error();
	exit();
} */
?>
<?php 
$db_conx_test = mysqli_connect("localhost", "root", "HockeyLife12", "scavengg_testing");
// Evaluate the connection
if (mysqli_connect_errno()) {
	echo mysqli_connect_error();
	exit();
}
?>