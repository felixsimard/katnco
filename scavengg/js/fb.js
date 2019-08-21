//FACEBOOK API
function statusChangeCallback(response) {
    if (response.status === 'connected') {
      fbapi();
      _("facebook_login").style.opacity = 0.5;
      _("facebook_login").onclick = function() { return true; }
      _("fb_checked").style.display = 'block';
      _("facebook_login").innerHTML = "<img src='https://facebookbrand.com/wp-content/themes/fb-branding/prj-fb-branding/assets/images/fb-art.png'> Connected";
      _("phone").disabled = false;
    } else if (response.status === 'not_authorized') {
      // The person is logged into Facebook, but not your app.
      //popLogin("open");
      _("facebook_login").style.opacity = 1;
      _("facebook_login").onclick = function() { fblogin(); }
      _("fb_checked").style.display = 'none';
      _("facebook_login").innerHTML = "<img src='https://facebookbrand.com/wp-content/themes/fb-branding/prj-fb-branding/assets/images/fb-art.png'> Continue with Facebook";
      _("phone").disabled = true;
    } else {
      // The person is not logged into Facebook, so we're not sure if
      // they are logged into this app or not.
      //popLogin("open");
      _("facebook_login").style.opacity = 1;
      _("facebook_login").onclick = function() { fblogin(); }
      _("fb_checked").style.display = 'none';
      _("facebook_login").innerHTML = "<img src='https://facebookbrand.com/wp-content/themes/fb-branding/prj-fb-branding/assets/images/fb-art.png'> Continue with Facebook";
      _("phone").disabled = true;
    }
}
function checkLoginState() {
    FB.getLoginStatus(function(response) {
      statusChangeCallback(response);
    });
}
window.fbAsyncInit = function() {
  FB.init({
    appId      : '516441172032939',
    status: true, 
    cookie: true,
    xfbml: true,
    oauth: true,
    version    : 'v2.10' 
    });

FB.getLoginStatus(function(response) {
    statusChangeCallback(response);
});

};

  // Load the SDK asynchronously
  (function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "//connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
  }(document, 'script', 'facebook-jssdk'));

function fblogin() {
     FB.login(function(response) {
      if(response.status == 'connected'){
        //popLogin("close");
        fbapi();    
        _("facebook_login").style.opacity = 0.5;
        _("facebook_login").onclick = function() { return true; }
        _("fb_checked").style.display = 'block';
        _("facebook_login").innerHTML = "<img src='https://facebookbrand.com/wp-content/themes/fb-branding/prj-fb-branding/assets/images/fb-art.png'> Connected";
        _("phone").disabled = false;
        return true;
      } else if(response.status == 'not_authorized'){
        popLogin("open");
        _("facebook_login").style.opacity = 1;
        _("facebook_login").onclick = function() { fblogin(); }
        _("fb_checked").style.display = 'none';
        _("facebook_login").innerHTML = "<img src='https://facebookbrand.com/wp-content/themes/fb-branding/prj-fb-branding/assets/images/fb-art.png'> Continue with Facebook";
        _("phone").disabled = true;
        return false;
      } else {
          popLogin("open");
          _("facebook_login").style.opacity = 1;
          _("facebook_login").onclick = function() { fblogin(); }
          _("fb_checked").style.display = 'none';
          _("facebook_login").innerHTML = "<img src='https://facebookbrand.com/wp-content/themes/fb-branding/prj-fb-branding/assets/images/fb-art.png'> Continue with Facebook";
          _("phone").disabled = true;
          return false;
      }
     }, {scope: 'public_profile, email, user_birthday, user_location'}); // the special scopes
}  

var uid = "";
function fbapi() {
    FB.api('/me', function(res1) {
        FB.api('/me?fields=email,first_name,age_range,birthday,picture,gender,location', function(res2) {
            var email = res2.email;
            var birthday = res2.birthday;
            var min_age = res2.age_range.min;
            var max_age = res2.age_range.max;
            var name = ''+res1.name+'';
            var fname = ''+res2.first_name+'';
            var gender = res2.gender;
            var pic = res2.picture.data.url;
            var location = res2.location;
            if(location) {
                var user_location = location.name;
            } else {
                var user_location = 'Montreal, Quebec';
            }
            var ajax = ajaxObj("POST", 'https://scavengg.com/php_parsers/login.php');
            ajax.onreadystatechange = function () {
                if (ajaxReturn(ajax) == true) {
                    if (ajax.responseText != "") {
                        var pics = document.getElementsByClassName("profile_pic");
                        for(var i=0; i < pics.length; i++) {
                            pics[i].style.background = 'url('+pic+') no-repeat center center';
                        }
                        _("welcome_mess").innerHTML = ''+fname+', what are you looking for?';
                        //Set global uid variable
                        uid = ajax.responseText;
                        checkPhone(uid);
                        updateSession(uid);
                        getPaymentMethod(uid);
                        userInfo('cart_content');
                        loading('close');
                        popup('countdown');
                    } else {     
                        popLogin('open');
                        popup('countdown');
                    }   
                }
            }
            ajax.send("action=user_signup&name="+name+"&email="+email+"&min_age="+min_age+"&max_age="+max_age+"&birthday="+birthday+"&pic="+pic+"&gender="+gender+"&location="+user_location);
        });         
    });     
}
/*---------------------------------------------------------------------------------------------------------------*/