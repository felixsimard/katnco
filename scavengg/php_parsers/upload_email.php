<?php 
include_once("../php_includes/db_conx.php");
include_once("../php_includes/session.php");
include_once("../class/time_ago_class.php");
include_once("../class/phone_format.php");
include_once("../class/number_format.php");
?>
<?php 
// UPLOAD FOR EMAIL NEWSLETTER
if($_FILES["news_file_settings"]["name"] != "") {

$temporary = explode(".", $_FILES["news_file_settings"]["name"]);
$file_extension = end($temporary);

if ($_FILES["news_file_settings"]["type"] == "image/jpeg" || $_FILES["news_file_settings"]["type"] == "image/jpg" || $_FILES["news_file_settings"]["type"] == "image/png") {
    if ($_FILES["news_file_settings"]["error"] > 0) {
        echo "Error uploading: " . $_FILES["news_file_settings"]["error"] ."";
    } else {
        //Upload file
        $db_file_name = rand(100000000000,999999999999).".".$file_extension;
        $sourcePath = $_FILES['news_file_settings']['tmp_name']; 
        $targetPath = "/var/www/news/".$db_file_name; 
        $move_result = move_uploaded_file($sourcePath, $targetPath);
        if($move_result == true) {

            /*GET EMAIL THAT HAS THE MOST RECENT 'added' field
            $query = mysqli_query($db_conx, "SELECT id FROM news ORDER BY added DESC LIMIT 1");
            $row = mysqli_fetch_row($query);
            $news_id = $row[0];
            */

            //UPDATE EMAIL PIC ROW
            //mysqli_query($db_conx, "UPDATE news SET picture='$db_file_name' WHERE id='$news_id' LIMIT 1");

            //Insert news into DB
            $sql = "INSERT INTO news (title, message, picture, added) VALUES ('','','$db_file_name',now())";
            $query = mysqli_query($db_conx, $sql);
            $news_id = mysqli_insert_id($db_conx);  

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
    echo 'upload_success'; // No picture associated to newsletter email...
    exit();
}

?>