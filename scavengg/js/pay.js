function pay(action, customer_bt, customer_db_id, amount, cart_rows, promotion) {
    var delivery = _("delivery_address").value;
    if(delivery == "") {
        status("You must provide a delivery address.", "black");
        _("delivery_address").focus();
    } else {
        loading('open');
        //alert(''+action+', '+customer_bt+', '+customer_db_id+', '+amount+', '+cart_rows+'');
        xmlhttp = new XMLHttpRequest();
        xmlhttp.open("GET","https://scavengg.com:7001?action="+action+"&customer_bt="+customer_bt+"&customer_db_id="+customer_db_id+"&amount="+amount+"&cart_rows="+cart_rows, true);
        xmlhttp.onreadystatechange=function(){
            if (xmlhttp.readyState==4 && xmlhttp.status==200){
                if(xmlhttp.responseText == "transaction_processed") {
                    afterTransaction(customer_bt, customer_db_id, amount, cart_rows, promotion);
                } else {
                    loading('close');
                    flyout('close');
                    status('Sorry, an error occurred while processing the transaction. Please contact support.', "black");
                }

                } 
        }
        xmlhttp.send();
    }
   
}
function afterTransaction(customer_bt, customer_db_id, amount, cart_rows, promotion) {
    var ajax = ajaxObj("POST", 'https://scavengg.com/php_parsers/payment.php');
    ajax.onreadystatechange = function () {
        if (ajaxReturn(ajax) == true) {
            var array = ajax.responseText.split('|');
            if(array[0] == 'transaction_completed') {
                loading('close');
                userInfo("view_cart");
                status("Success! An email receipt was sent to your inbox. Thank you.");
                //var msg = "Hey "+array[3]+", your Scavengg order has been received and is being processed. We sent you an email receipt at "+array[1]+". Your order number is #"+array[4]+"";
                //sms("+1"+array[2]+"", "Order Confirmation #"+array[4]+"", "Scavengg", msg); 
            } else {
                loading('close');
                flyout('close');
                status('An error occurred while completing the transaction. Please contact support.', "black");
            }
        }
    }
    ajax.send("action=update_cart_after_transaction&customer_bt="+customer_bt+"&customer_db_id="+customer_db_id+"&amount="+amount+"&cart_rows="+cart_rows+"&promotion="+promotion);
}