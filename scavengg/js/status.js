function status(msg, bg) {
    var bar = _("status_bar");
    bar.style.top = 0+'px';
    if(bg == "black") {
        bar.style.background = '#ff5050'; 
        bar.style.color = 'white';
    } else {
        bar.style.background = '#61d395';
        bar.style.color = 'white';
    }
    bar.innerHTML = ''+msg+'';
    setTimeout(function() {
        bar.style.top = -50+'px';
        bar.innerHTML = '';
    }, 4500);
}