$(document).ready(function (e) {
$("#update_settings_image").on('submit',(function(e) {
e.preventDefault();

$.ajax({
  xhr: function()
  {
    var xhr = new window.XMLHttpRequest();
    var percentComplete = 0;
    //Upload progress
    xhr.upload.addEventListener("progress", function(evt){
      if (evt.lengthComputable) {
        var percentComplete = (evt.loaded / evt.total)*100;
        //$("#update_file_selector_settings").css("opacity", 0.5);
        //alert(percentComplete);
        //status("Uploading profile picture ("+Math.round(percentComplete)+"%)", "black");
        document.getElementById("update_item_btn").innerHTML = ''+Math.round(percentComplete)+'%';
        if(Math.round(percentComplete) == 100) {
          //$("#update_file_selector_settings").css("opacity", 1);
          //status("Upload Complete", "black");
          document.getElementById("update_item_btn").innerHTML = 'Update';
        }

      }
    }, false);
    //Download progress
    xhr.addEventListener("progress", function(evt){
      if (evt.lengthComputable) {
        var percentComplete = evt.loaded / evt.total;
        //Do something with download progress
        //console.log(percentComplete);
      }
    }, false);
    return xhr;
  },
url: "php_parsers/upload2.php", // Url to which the request is send
type: "POST",             // Type of request to be send, called as method
data: new FormData(this), // Data sent to server, a set of key/value pairs (i.e. form fields and values)
contentType: false,       // The content type used when sending data to the server.
cache: false,             // To unable request pages to be cached
processData:false,        // To send DOMDocument or non processed data file it is set to false
success: function(data)   // A function to be called if request succeeds
{

if(data == "Not_valid_file_type") {
    $('#update_file_selector_settings').attr('src', "https://scavengg.com/photos/default_pic.png");
    loading("close");
    status("Please choose a valid image file to upload.", "black");
} else if(data == "Error_uploading_image") {
    $('#update_file_selector_settings').attr('src', "https://scavengg.com/photos/default_pic.png");
    loading("close");
    status("Oh no! An error occurred while uploading your image...", "black");
} else if(data == "upload_success") {
    //
    flyout('close');
    loading("close");
    status("Item updated successfully.");
    getStore();
} else {
    loading("close");
    status(""+data+"", "black");
}

}
});
}));

// Function to preview image after validation
$(function() {
$("#update_file_settings").change(function() {

var file = this.files[0];
var imagefile = file.type;
var match= ["image/jpeg","image/png","image/jpg"];
if(!((imagefile==match[0]) || (imagefile==match[1]) || (imagefile==match[2])))
{
loading("close");
status("Please select a valid image file.", "black");
}
else
{
var reader = new FileReader();
reader.onload = imageIsLoaded;
reader.readAsDataURL(this.files[0]);
}
});
});
function imageIsLoaded(e) {

$('#update_avatar_preview').css("display", "block");
$('#update_file_selector_settings').attr('src', e.target.result);
//
};
});