function sms(to, name, sender, msg) {
   xmlhttp = new XMLHttpRequest();
   xmlhttp.open("GET","https://scavengg.com:5001?to="+to+"&name="+name+"&sender="+sender+"&msg="+msg, true);
   xmlhttp.onreadystatechange=function(){
    if (xmlhttp.readyState==4 && xmlhttp.status==200){
           if(xmlhttp.responseText == "msg_sent") {
               return true;
           } else {
               alert("Error sending text message");
           }

        } 
   }
   xmlhttp.send();
}