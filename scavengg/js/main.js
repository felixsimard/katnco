function _(x) {
	return document.getElementById(x);
}
function _class(x) {
	return document.getElementsByClassName(x);
}
var constant_check = setInterval(function() { return true; }, 1000); 

function popLogin(action, context) {
    if(action == "open") {
		flyout('close');
        _("login").style.opacity = 1;
        _("login").style.zIndex = 10000;
        _("search_results").style.display = 'none'; 
        if(context == "countdown_launch") {
            _("payment_login_box").style.display = 'none';
            _("facebook_phone_login_box").className = 'col-md-12';
            constant_check = setInterval(function() { activated(context); }, 1000); 
        } else {
            _("facebook_phone_login_box").className = 'col-md-6';
            _("payment_login_box").style.display = 'block';
            constant_check = setInterval(function() { activated(); }, 1000); 
        }
    } else if(action == "close") {
        _("login").style.opacity = 0;
        _("login").style.zIndex = -1;
        _("search_results").style.display = 'block'; 
        clearInterval(constant_check);
    }
}
function updateSession(uid) {
    var ajax = ajaxObj("POST", 'https://scavengg.com/php_includes/session.php');
    ajax.onreadystatechange = function () {
        if (ajaxReturn(ajax) == true) {
            uid = ajax.responseText;
        }
    }
    ajax.send("uid="+uid);
}
function welcome_msg_seen() {
    var ajax = ajaxObj("POST", 'https://scavengg.com/php_parsers/user.php');
    ajax.onreadystatechange = function () {
        if (ajaxReturn(ajax) == true) {
            popup('close');
        }
    }
    ajax.send("action=welcome_msg_seen");
}
function welcome_msg() {
    // Also use this function to set year date
    var currentTime = new Date();
    var year = currentTime.getFullYear();
    _("year").innerHTML = ''+year+'';
    //
    var ajax = ajaxObj("POST", 'https://scavengg.com/php_parsers/user.php');
    ajax.onreadystatechange = function () {
        if (ajaxReturn(ajax) == true) {
            if(ajax.responseText.trim() == "show") {
                popup("welcome_info");
            } else {
                popup('close');
            }
        }
    }
    ajax.send("action=check_welcome_msg");
}
function popup(action) {
    var pop_div = _("popup_div");
    var page = _("page");
    if(action == "countdown") {
        pop_div.style.display = 'block';
        page.style.overflowY = 'hidden'; 
        countdown();
    } else if(action == "welcome_info") {
        pop_div.style.display = 'block';
        page.style.overflowY = 'hidden'; 
        _("popup").innerHTML = '<img class="logo" src="https://scavengg.com/photos/logo.png"><br><br><div class="logo_title">Welcome to SCAVENGG</div><hr>Searching for streetwear? Browse our selection or place a custom order. Either way, we&#39;ll find what you&#39;re looking for. <hr>All prices are in CAD.<hr><button class="review_cart" style="float:none;" onclick="welcome_msg_seen()">Done</button>';
    } else if(action == "close") {
        pop_div.style.display = 'none';
        page.style.overflowY = 'auto';
    }
}

var profile_check = false;
function activated(context) {
    if(context == "") {
        var context = 'normal';
    }
    var ajax = ajaxObj("POST", 'https://scavengg.com/php_parsers/user.php');
    ajax.onreadystatechange = function () {
        if (ajaxReturn(ajax) == true) {
            var response = ajax.responseText.trim();
            if(response == "not_activated_no_fb") {
                //status("Connect to Facebook. Add your phone number and your payment method.");
                profile_check = false;
            } else if(response == "not_activated_no_bt_no_phone") {
                //status("Please enter your phone number and chose your payment method.");
                profile_check = false;
            } else if(response == "not_activated_no_phone") {
                //status("Please enter your phone number.");
                profile_check = false;
            } else if(response == "not_activated_no_bt") {
                //status("Please add your payment method.");
                profile_check = false;
            } else if(response == "no_activated_not_allowed") {
                profile_check = false;
                popLogin('close');
                status("Your account has not been activated yet. Please come back later.", "black");
                loading("close");
            } else if(response == "activated_for_launch") {
                profile_check = true;
                popLogin('close');
                checkPhone(uid);
            } else if(response == "activated") { // activated
                profile_check = true;
                popLogin('close');
                checkPhone(uid);
            }
            console.log("Activated: " + profile_check);
        }
    }
    ajax.send("action=check_activated&context="+context);
}

function loading(action) {
    var load = _("loading");
    var page = _("page");
    if(action == "open") {
        load.style.left = '0px';
        page.style.overflowY = 'hidden';
    } else if(action == "close") {  
        load.style.left = '-100vw';
        page.style.overflowY = 'auto';
    } else {
        return true;
    }
}

function decimals(num) {
    var value = Math.round(num * 100) / 100;
    return value.toFixed(2);
}


function autocomplete(inp, arr) {
  /*the autocomplete function takes two arguments,
  the text field element and an array of possible autocompleted values:*/
  var currentFocus;
  /*execute a function when someone writes in the text field:*/
  inp.addEventListener("input", function(e) {
      var a, b, i, val = this.value;
      /*close any already open lists of autocompleted values*/
      closeAllLists();
      if (!val) { return false;}
      currentFocus = -1;
      /*create a DIV element that will contain the items (values):*/
      a = document.createElement("DIV");
      a.setAttribute("id", this.id + "autocomplete-list");
      a.setAttribute("class", "autocomplete-items");
      /*append the DIV element as a child of the autocomplete container:*/
      this.parentNode.appendChild(a);
      /*for each item in the array...*/
      for (i = 0; i < arr.length; i++) {
        /*check if the item starts with the same letters as the text field value:*/
        if (arr[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
          /*create a DIV element for each matching element:*/
          b = document.createElement("DIV");
          /*make the matching letters bold:*/
          b.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>";
          b.innerHTML += arr[i].substr(val.length);
          /*insert a input field that will hold the current array item's value:*/
          b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";
          /*execute a function when someone clicks on the item value (DIV element):*/
          b.addEventListener("click", function(e) {
              /*insert the value for the autocomplete text field:*/
              inp.value = this.getElementsByTagName("input")[0].value;
              /*close the list of autocompleted values,
              (or any other open lists of autocompleted values:*/
              closeAllLists();
          });
          a.appendChild(b);
        }
      }
  });
  /*execute a function presses a key on the keyboard:*/
  inp.addEventListener("keydown", function(e) {
      var x = document.getElementById(this.id + "autocomplete-list");
      if (x) x = x.getElementsByTagName("div");
      if (e.keyCode == 40) {
        /*If the arrow DOWN key is pressed,
        increase the currentFocus variable:*/
        currentFocus++;
        /*and and make the current item more visible:*/
        addActive(x);
      } else if (e.keyCode == 38) { //up
        /*If the arrow UP key is pressed,
        decrease the currentFocus variable:*/
        currentFocus--;
        /*and and make the current item more visible:*/
        addActive(x);
      } else if (e.keyCode == 13) {
        /*If the ENTER key is pressed, prevent the form from being submitted,*/
        e.preventDefault();
        if (currentFocus > -1) {
          /*and simulate a click on the "active" item:*/
          if (x) x[currentFocus].click();
        }
      }
  });
  function addActive(x) {
    /*a function to classify an item as "active":*/
    if (!x) return false;
    /*start by removing the "active" class on all items:*/
    removeActive(x);
    if (currentFocus >= x.length) currentFocus = 0;
    if (currentFocus < 0) currentFocus = (x.length - 1);
    /*add class "autocomplete-active":*/
    x[currentFocus].classList.add("autocomplete-active");
  }
  function removeActive(x) {
    /*a function to remove the "active" class from all autocomplete items:*/
    for (var i = 0; i < x.length; i++) {
      x[i].classList.remove("autocomplete-active");
    }
  }
  function closeAllLists(elmnt) {
    /*close all autocomplete lists in the document,
    except the one passed as an argument:*/
    var x = document.getElementsByClassName("autocomplete-items");
    for (var i = 0; i < x.length; i++) {
      if (elmnt != x[i] && elmnt != inp) {
        x[i].parentNode.removeChild(x[i]);
      }
    }
  }
  /*execute a function when someone clicks in the document:*/
  document.addEventListener("click", function (e) {
      closeAllLists(e.target);
      });
}

function countdown() {
    
    //Remove focus from order input
    _("order_input").blur();

    // Set the date we're counting down to
    var countDownDate = new Date("May 19, 2018 16:00:00").getTime();

    var now = new Date().getTime();
    if(countDownDate - now < 0) {
        popup('close');
        welcome_msg();
        activated();
    } else {    
        activated('countdown_launch');
    }
    // Update the count down every 1 second
    var x = setInterval(function() {

        // Get todays date and time
        var now = new Date().getTime();
        
        // Find the distance between now an the count down date
        var distance = countDownDate - now;
        
        // Time calculations for days, hours, minutes and seconds
        var days = Math.floor(distance / (1000 * 60 * 60 * 24));
        var hours = Math.floor(((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)) + days*24);
        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((distance % (1000 * 60)) / 1000);

        _("popup").innerHTML = '<img class="logo" src="https://scavengg.com/photos/logo.png"><br><br><div class="logo_title">SCAVENGG</div><hr><div id="cd_time"></div><hr>'; 

        if(profile_check == false) {
            _("popup").innerHTML += '<button class="review_cart" style="float:none;" onclick="popLogin(\'open\', \'countdown_launch\')">Sign Up</button>';
        } else {
            _("popup").innerHTML += '<div style="color:#61d395;">Ready for launch.</div>';
        }

        // Output the result in an element with id="cd_time"
        //_("cd_time").innerHTML = days + "d " + hours + "h " + minutes + "m " + seconds + "s ";
        _("cd_time").innerHTML = hours + "h " + minutes + "m " + seconds + "s ";
        
        // count down over
        if (distance < 0) {
            clearInterval(x);
            _("cd_time").innerHTML = "Welcome to Scavengg";
            popup('close');
            welcome_msg();
        }
    }, 500);
}