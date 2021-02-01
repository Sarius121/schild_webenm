function onTabClicked(tabHeader, tabName){
    $("#menu-tab ul.body").removeClass('visible');
    document.getElementById(tabName).classList.add("visible");

    $("#menu-tab ul.header li").removeClass('active');
    $(tabHeader).addClass('active');
}

function onMenuItemClicked(item, action){

    switch(action){
        case "create-backup":
            window.open("backup-file.php?action=create");
            break;
        case "restore-backup":
            document.getElementById("backupFile").click();
            break;
            
    }
}

function onRestoreBackupFileSelected(files){
    if(files.length > 0){
        console.log(files[0]);
    }
}