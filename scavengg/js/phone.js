function checkPhone(uid) {
    var ajax = ajaxObj("POST", 'https://scavengg.com/php_parsers/user.php');
    ajax.onreadystatechange = function () {
        if (ajaxReturn(ajax) == true) {
            var array = ajax.responseText.trim().split('|');
            if(array[0] == 'no_phone') {
                _("phone_checked").style.display = 'none';
                //_("phone").disabled = false;
                _("phone").value = '';
            } else {
                _("phone_checked").style.display = 'block';
                //_("phone").disabled = true;
                _("phone").value = array[0];
                if(array[1] == "send_sms") {
                    var msg = "Welcome to Scavengg. Searching for streetwear? Browse our selection or place a custom order. Either way, we'll find what you're looking for. https://scavengg.com/";
                    sms("+1"+array[0]+"", "Welcome SMS from Scavengg", "Scavengg", msg); 
                    savePhone(array[0], "welcome_sms_done");
                }
            }
        }
    }
    ajax.send("action=check_phone&uid="+uid);
}
function savePhone(num, action2, uid) {
    if(num.length == 10) {
        var ajax = ajaxObj("POST", 'https://scavengg.com/php_parsers/user.php');
        ajax.onreadystatechange = function () {
            if (ajaxReturn(ajax) == true) {
                if(ajax.responseText == '') {
                    return true;
                } else {
                    //status("Phone number saved successfully.");
                    return true;
                }
            }
        }
        ajax.send("action=save_phone&phone="+num+"&uid="+uid+"&action2="+action2);
    }
}