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

restoringBackup = false;

window.onbeforeunload = function(){
    console.log(restoringBackup);
    if(restoringBackup){
        console.log("prevent");
        return "Das Backup wird gerade eingelesen. Wenn du das Fenster schließt, wird der Vorgang abgebrochen.";
    } else {
        return;
    }
}

function onRestoreBackupFileSelected(files){
    if(files.length > 0){
        console.log(files[0]);

        var file = files[0];

        var reader = new FileReader();

        var senddata = new Object();
        senddata.name = file.name;
        senddata.date = file.lastModified;
        senddata.size = file.size;
        senddata.type = file.type;

        reader.onload = function(fileData){
            senddata.fileData = fileData.target.result;

            //send file
            var httpRequest = new XMLHttpRequest();
            httpRequest.open("POST", "backup-file.php?action=restore");
            httpRequest.onreadystatechange = function(){
                if (this.readyState == 4 && this.status == 200) {
                    //success
                    restoringBackup = false;
                    if(this.responseText == "success"){
                        $("#upload-backup-modal").addClass("ready");
                        $("#restoring-backup-status").text("Das Backup wurde erfolgreich hochgeladen. Wenn sich diese Seite nicht automatisch neu lädt, lade die Seite manuell neu, um das hochgeladene Backup zu sehen.");
                        location.reload();
                    } else {
                        $("#upload-backup-modal").addClass("failed");
                        $("#restoring-backup-status").text("Es ist ein Fehler aufgetreten! Möglicherweise ist die Datei keine Notendatei.");
                        console.log(this.responseText);
                    }
                } else if(this.readyState == 4) {
                    restoringBackup = false;
                    //error
                    $("#upload-backup-modal").addClass("failed");
                    $("#restoring-backup-status").text("Es ist ein Fehler aufgetreten! Kontaktiere den Administrator.");
                }
            }
            $("#upload-backup-modal").modal();
            restoringBackup = true;
            httpRequest.send(senddata);
        }

        reader.readAsDataURL(file);
    }
}