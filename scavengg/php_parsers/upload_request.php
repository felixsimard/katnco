<?php 
include_once("../php_includes/db_conx.php");
include_once("../php_includes/session.php");
include_once("../class/time_ago_class.php");
include_once("../class/phone_format.php");
include_once("../class/number_format.php");
?>
<?php 
// UPLOAD FOR REQUESTS
if($_FILES["requests_file_settings"]["name"] != "") {

$temporary = explode(".", $_FILES["requests_file_settings"]["name"]);
$file_extension = end($temporary);

if ($_FILES["requests_file_settings"]["type"] == "image/jpeg" || $_FILES["requests_file_settings"]["type"] == "image/jpg" || $_FILES["requests_file_settings"]["type"] == "image/png") {
    if ($_FILES["requests_file_settings"]["error"] > 0) {
        echo "Error uploading: " . $_FILES["requests_file_settings"]["error"] ."";
    } else {
        //Upload file
        $db_file_name = rand(100000000000,999999999999).".".$file_extension;
        $sourcePath = $_FILES['requests_file_settings']['tmp_name']; 
        $targetPath = "/var/www/requests/".$db_file_name; 
        $move_result = move_uploaded_file($sourcePath, $targetPath);
        if($move_result == true) {

            //GET REQUEST THAT HAS THE MOST RECENT 'added' field
            $query = mysqli_query($db_conx, "SELECT id FROM requests ORDER BY added DESC LIMIT 1");
            $row = mysqli_fetch_row($query);
            $request_id = $row[0];

            //SAVE REQUESTS IMAGE
            mysqli_query($db_conx, "UPDATE requests SET picture='$db_file_name' WHERE id='$request_id' LIMIT 1");

            echo 'upload_success';
            exit();
        } else {
            echo 'Error_uploading_image';
            exit();
        }
    }

}   else {
        echo "Not_valid_file_type";
        exit();
    }   

} else { 
    echo 'upload_success';
    exit();
}

?>