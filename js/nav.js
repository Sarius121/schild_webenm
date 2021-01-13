function onNavButtonClicked(btn, data){
    $("#data-container > div").removeClass('visible');
    document.getElementById(data).classList.add("visible");

    //add active class to nav-link
    $("#nav-data .nav-link").removeClass('active');
    btn.classList.add('active');
}