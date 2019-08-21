<?php 
include_once("../php_includes/db_conx.php");
include_once("../php_includes/session.php");
include_once("../class/time_ago_class.php");
include_once("../class/phone_format.php");
include_once("../class/number_format.php");
?>
<?php 
// UPLOAD FOR ITEM UPDATES
if($_FILES["update_file_settings"]["name"] != "") {

$temporary = explode(".", $_FILES["update_file_settings"]["name"]);
$file_extension = end($temporary);

if ($_FILES["update_file_settings"]["type"] == "image/jpeg" || $_FILES["update_file_settings"]["type"] == "image/jpg" || $_FILES["update_file_settings"]["type"] == "image/png") {
    if ($_FILES["update_file_settings"]["error"] > 0) {
        echo "Error uploading: " . $_FILES["update_file_settings"]["error"] ."";
    } else {
        //Upload file
        $db_file_name = rand(100000000000,999999999999).".".$file_extension;
        $sourcePath = $_FILES['update_file_settings']['tmp_name']; 
        $targetPath = "/var/www/store/".$db_file_name; 
        $move_result = move_uploaded_file($sourcePath, $targetPath);
        if($move_result == true) {

            //GET ITEM THAT HAS THE MOST RECENT 'added' field
            $query = mysqli_query($db_conx, "SELECT id, pic FROM store ORDER BY added DESC LIMIT 1");
            $row = mysqli_fetch_row($query);
            $item_id = $row[0];
            $current_pic = $row[1];

            //Delete the old image for that item
            unlink('/var/www/store/'.$current_pic.'');

            //UPDATE PRODUCT PIC
            mysqli_query($db_conx, "UPDATE store SET pic='$db_file_name' WHERE id='$item_id' LIMIT 1");

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
    echo 'upload_success'; // probably because updated item without changing picture...
    exit();
}

?>