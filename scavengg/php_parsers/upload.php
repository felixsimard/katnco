<?php 
include_once("../php_includes/db_conx.php");
include_once("../php_includes/session.php");
include_once("../class/time_ago_class.php");
include_once("../class/phone_format.php");
include_once("../class/number_format.php");
?>
<?php 
// UPLOAD FOR NEW ITEM UPLOAD
if($_FILES["file_settings"]["name"] != "") {

$temporary = explode(".", $_FILES["file_settings"]["name"]);
$file_extension = end($temporary);

if ($_FILES["file_settings"]["type"] == "image/jpeg" || $_FILES["file_settings"]["type"] == "image/jpg" || $_FILES["file_settings"]["type"] == "image/png") {
    if ($_FILES["file_settings"]["error"] > 0) {
        echo "Error uploading: " . $_FILES["file_settings"]["error"] ."";
    } else {
        //Upload file
        $db_file_name = rand(100000000000,999999999999).".".$file_extension;
        $sourcePath = $_FILES['file_settings']['tmp_name']; 
        $targetPath = "/var/www/scavengg/store/".$db_file_name; 
        $move_result = move_uploaded_file($sourcePath, $targetPath);
        if($move_result == true) {

            //Get last post in store with no pic attached to it yet
            $query = mysqli_query($db_conx_test, "SELECT id FROM store ORDER BY added DESC LIMIT 1");
            $row = mysqli_fetch_row($query);
            $item_id = $row[0];

            //UPDATE PRODUCT PIC
            mysqli_query($db_conx_test, "UPDATE store SET pic='$db_file_name' WHERE id='$item_id' LIMIT 1");

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
}

?>