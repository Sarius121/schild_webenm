function onNavButtonClicked(btn, data){
    $("#data-container > div").removeClass('visible');
    document.getElementById(data).classList.add("visible");

    //add active class to nav-link
    $("#nav-data .nav-link").removeClass('active');
    btn.classList.add('active');

    //adjust possible sort methods
    switch(data){
        case "data-grades":
            $("#sort-menu-items").toggleClass("disabled", false);
            break;
        case "data-class-teacher":
            $("#sort-menu-items").toggleClass("disabled", true);
            break;
        case "data-exams":
            $("#sort-menu-items").toggleClass("disabled", false);
            break;
    }
}