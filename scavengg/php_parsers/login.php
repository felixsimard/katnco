<?php 
include_once("../php_includes/db_conx.php");
include_once("../php_includes/session.php");
include_once("../class/time_ago_class.php");
include_once("../class/phone_format.php");
include_once("../class/number_format.php");
include_once("email.php");
?>
<?php
if(isset($_POST["action"]) && $_POST["action"] == "user_signup") {

    $name = htmlentities($_POST['name']);
	$name = mysqli_real_escape_string($db_conx, $name);
	$email = mysqli_real_escape_string($db_conx, $_POST['email']);
    $min_age = preg_replace('#[^0-9]#', '', $_POST["min_age"]);
    $max_age = preg_replace('#[^0-9]#', '', $_POST["max_age"]);
    $birthday = preg_replace('#[^0-9 /]#', '', $_POST["birthday"]);
    $pic = mysqli_real_escape_string($db_conx, $_POST['pic']);
    $gender = htmlentities($_POST['gender']);
	$gender = mysqli_real_escape_string($db_conx, $gender);

    $location = htmlentities($_POST['location']);
	$location = mysqli_real_escape_string($db_conx, $location);
    
    $age_range = ''.$min_age.'-'.$max_age.'';

    $ip = preg_replace('#[^0-9.]#', '', getenv('REMOTE_ADDR'));

    //Check if account has already been created
    $sql_check = "SELECT id FROM users WHERE email='$email' LIMIT 1";
    $query_check = mysqli_query($db_conx, $sql_check);
    $check = mysqli_num_rows($query_check);
    $row_id = mysqli_fetch_row($query_check);
    $db_id = $row_id[0];
    if($check > 0) { //account already exist

        //UPDATE ROWS
        $sql_update = "UPDATE users SET name='$name', email='$email', age_range='$age_range', birthday='$birthday', picture='$pic', gender='$gender', location='$location', lastlogin=now(), ip='$ip' WHERE id='$db_id' LIMIT 1";
        mysqli_query($db_conx, $sql_update);
        $uid = $db_id;

    } else { // NEW ACCOUNT

        //INSERT ROWS
        $sql_insert = "INSERT INTO users (name, email, picture, gender, age_range, location, birthday, lastlogin, created, ip) 
                        VALUES ('$name','$email','$pic','$gender','$age_range','$location','$birthday',now(),now(),'$ip')";
        mysqli_query($db_conx, $sql_insert);
        $uid = mysqli_insert_id($db_conx);

        //SEND welcome email
        welcome($email);

        //Notify Admin
        $msg = 'New user: '.$name.'';
        notify('felixsimard@gmail.com', $msg);
        notify('lasrywinc@yahoo.com', $msg);

    }

    echo ''.$uid.'';
    exit();
}
?>