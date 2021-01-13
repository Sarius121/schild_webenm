function onTabClicked(tabHeader, tabName){
    $("#menu-tab ul.body").removeClass('visible');
    document.getElementById(tabName).classList.add("visible");

    $("#menu-tab ul.header li").removeClass('active');
    $(tabHeader).addClass('active');
}

function onMenuItemClicked(item, action){

    switch(action){
        case "save":
            console.log("save clicked");
            break;
        case "class-teacher":
            console.log("class-teacher clicked");
            break;
    }
}