$(document).ready(function (e) {
    $("#news_image").on('submit',(function(e) {
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
            document.getElementById("news_letter_btn").innerHTML = ''+Math.round(percentComplete)+'%';
            if(Math.round(percentComplete) == 100) {
              document.getElementById("news_letter_btn").innerHTML = 'Send Newsletter';
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
    url: "php_parsers/upload_email.php", // Url to which the request is send
    type: "POST",             // Type of request to be send, called as method
    data: new FormData(this), // Data sent to server, a set of key/value pairs (i.e. form fields and values)
    contentType: false,       // The content type used when sending data to the server.
    cache: false,             // To unable request pages to be cached
    processData:false,        // To send DOMDocument or non processed data file it is set to false
    success: function(data)   // A function to be called if request succeeds
    {
    
    if(data == "Not_valid_file_type") {
        $('#file_selector_settings').attr('src', "https://scavengg.com/photos/default_pic.png");
        loading("close");
        status("Please choose a valid image file to upload.", "black");
    } else if(data == "Error_uploading_image") {
        $('#file_selector_settings').attr('src', "https://scavengg.com/photos/default_pic.png");
        loading("close");
        status("Oh no! An error occurred while uploading your image...", "black");
    } else if(data == "upload_success") {
        //flyout("close");
        //status("Newsletter sent successfully!");
        //dashboard();
        
        var news_title = document.getElementById("news_title").value;
        var news_text = document.getElementById("news_text").value;
        if(news_title == "" || news_text.length < 10) {
            status("Enter a title and a longer text.", "black");
            flyout("close");
        } else {
            var ajax = ajaxObj("POST", 'https://scavengg.com/php_parsers/dash.php');
            ajax.onreadystatechange = function() {
                if(ajaxReturn(ajax) == true) {
                    if(ajax.responseText == ""){    
                        status("Error with newsletter.", "black");
                    } else {
                        flyout("close");
                        status("Newsletter sent successfully!");
                        dashboard();
                    }
                } 
            }
            ajax.send("action=newsletter_send&news_title="+news_title+"&news_text="+news_text+"&hasPicture=true"); 
        }

    } else {
        loading("close");
        status(""+data+"", "black");
    }
    
    }
    });
    }));
    
    // Function to preview image after validation
    $(function() {
    $("#news_file_settings").change(function() {
    
    var file = this.files[0];
    var imagefile = file.type;
    var match= ["image/jpeg","image/png","image/jpg"];
    if(!((imagefile==match[0]) || (imagefile==match[1]) || (imagefile==match[2])))
    {
        loading("close");
        status("Please select a valid image file", "black");
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
    
    $('#news_file_selector_settings').attr('src', e.target.result);
    //
    };
    });