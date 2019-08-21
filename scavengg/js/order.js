function item(item_id, obj) {
    //var ajax = ajaxObj("POST", 'https://scavengg.com/php_parsers/item.php');
    var ajax = ajaxObj("POST", 'https://katnco.ca/scavengg/php_parsers/item.php');
    ajax.onreadystatechange = function () {
        if (ajaxReturn(ajax) == true) {
            if(ajax.responseText == "") {
                status("An error occurred, sorry.", "black");
                flyout('close');
            } else {
                _(obj).innerHTML = ajax.responseText;
            }
        }
    }
    ajax.send("action=get_item&item_id="+item_id);
}
function updateDisplayedPrice(item_id, size, elem) {
    //var ajax = ajaxObj("POST", 'https://scavengg.com/php_parsers/item.php');
    var ajax = ajaxObj("POST", 'https://katnco.ca/scavengg/php_parsers/item.php');
    ajax.onreadystatechange = function () {
        if (ajaxReturn(ajax) == true) {
            _(""+elem+"").value = ajax.responseText;
            status("Updated price for size "+size+"");
        }
    }
    ajax.send("action=update_displayed_price&item_id="+item_id+"&size="+size);
}
var categories = [];
var max_price = 2500;

function search(event, terms, categories, max_price, action2) {
    _("search_loader").style.opacity = 1;
    if(terms == "" && categories.length == 0 && action2 != "custom_request") {
        suggested(max_price, window.innerWidth);
        setTimeout(function() {
            _("search_loader").style.opacity = 0;
        }, 2000);
    } else {
        //var ajax = ajaxObj("POST", 'https://scavengg.com/php_parsers/search.php');
        var ajax = ajaxObj("POST", 'https://katnco.ca/scavengg/php_parsers/search.php');
        ajax.onreadystatechange = function () {
            if (ajaxReturn(ajax) == true) {
                _("search_results").innerHTML = ajax.responseText;
                $.getScript("js/upload_request.js", function() {
                    return true;
                });
                setTimeout(function() {
                    _("search_loader").style.opacity = 0;
                }, 2000);
            }
        }
        ajax.send("action=search_store&terms="+terms+"&categories="+categories+"&max_price="+max_price+"&action2="+action2);
    }
}
function suggested(max_price, viewport_size) {
    var displayed_items = '';
    var items_visible = document.getElementsByClassName('col-md-4 col-xs-6 search_grid_item');
    if(items_visible.length > 0) {
        for(var i=0; i < items_visible.length - 1; i++) {
            var item_id = items_visible[i].id.split('displayed_item_')[1];
            displayed_items += ''+item_id+' ';
        }
    }
    var ajax = ajaxObj("POST", 'https://scavengg.com/php_parsers/search.php');
    ajax.onreadystatechange = function () {
        if (ajaxReturn(ajax) == true) {
            var suggestions = ajax.responseText;
            _("search_results").insertAdjacentHTML('beforeend', suggestions); // use insertAdjacentHTML for Safari compatibility
            _("order_input").value = '';
        }
    }
    ajax.send("action=search_suggested&max_price="+max_price+"&displayed_items="+displayed_items+"&viewport_size="+viewport_size);
}

/*
function finalPrice(brut) {
    var tps = brut * 0.09975;
    var tvq = brut * 0.05;
    var price_with_taxes = brut + tps + tvq;
    //var service_fee = price_with_taxes * 0.10
    var final_price = Math.round((price_with_taxes) / 5) * 5; // round to nearest 5
    return final_price;
}
*/

function customRequest() {
    activated();
    if(profile_check == false) {
        popLogin('open');
    } else if(profile_check == "not_activated") { 
        status("Your account has not been activated yet or is missing information.", "black");
    } else {
        var caption = _("custom_order_desc").value;
        var size = _("custom_order_size").value;
        var brand = _("custom_order_brand").value; //optional
        var season = _("custom_order_season").value; //optional
        var pic = _("requests_file_selector_settings").src; //optional
        if(caption == "" || size == "") {
            status("Enter a description of what you want and the size.", "black"); 
        } else {
            loading("open");
            var ajaxsend = "action=request_item&caption="+caption+"&size="+size;
            if(brand != "") {
                ajaxsend += ""+"&brand="+brand;
            }
            if(season != "") {
                ajaxsend += ""+"&season="+season;
            }
            var ajax = ajaxObj("POST", 'https://scavengg.com/php_parsers/requests.php');
            ajax.onreadystatechange = function () {
                if (ajaxReturn(ajax) == true) {
                    var array = ajax.responseText.split("|");
                    if(array[0] == 'request_success') {
                        if(pic != "https://scavengg.com/photos/img_placeholder.png") {  // if theres an image, upload it 
                            $('#requests_image').submit();
                        } else { // no images attached
                            loading("close");
                            suggested(2500, window.innerWidth);
                            status("Your request has been received. You will be notified when we find what you want.");
                        }
                        var user_phone = array[1];
                        var user_name = array[2];
                        var msg = ''+user_name+', your custom order for "'+caption+'" has been received and is being processed. You will be notified when we find your item. Thank you.';
                        sms("+1"+user_phone+"", "Custom Request", "Scavengg", msg);
                    } else {
                        loading("close");
                        status("Error processing custom request.", "black");
                    }
                }
            }
            ajax.send(ajaxsend);
        }
    }
}