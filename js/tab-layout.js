function onTabClicked(tabHeader, tabName){
    $("#menu-tab ul.body").removeClass('visible');
    document.getElementById(tabName).classList.add("visible");
    activeTab = tabName;
    $("#menu-tab ul.header li").removeClass('active');
    $(tabHeader).toggleClass('active');
}