function onTabClicked(tabHeader, tabName){
    $("#menu-tab ul.body").removeClass('visible');
    document.getElementById(tabName).classList.add("visible");

    $("#menu-tab ul.header li").removeClass('active');
    $(tabHeader).addClass('active');
}

function onMenuItemClicked(item, action){
    if(item.classList.contains("disabled")){
        return;
    }

    switch(action){
        case "create-backup":
            window.open("backup-file.php?action=create");
            break;
        case "restore-backup":
            document.getElementById("backupFile").click();
            break;
        case "undo-backup":
            undoBackupRestore();
            break;
        case "information":
            $("#information-modal").modal("show");
            break;
    }
}

preventAppClosing = false;

window.onbeforeunload = function(){
    if(preventAppClosing){
        console.log("prevent");
        return "Ein Vorgang ist nicht beendet. Wenn du das Fenster schließt, wird der Vorgang abgebrochen.";
    } else {
        return;
    }
}

function onRestoreBackupFileSelected(files){
    if(files.length > 0){
        var formData = new FormData();
        formData.append("backupFile", files[0]);

        console.log(formData);

        //send file
        var httpRequest = new XMLHttpRequest();
        httpRequest.open("POST", "backup-file.php?action=restore");
        httpRequest.onreadystatechange = function(){
            if (this.readyState == 4 && this.status == 200) {
                //success
                preventAppClosing = false;
                if(this.responseText == "success"){
                    messageBox.setStatus(ProgressMessageBox.STATUS_SUCCESS);
                    messageBox.setMessage("Das Backup wurde erfolgreich hochgeladen. Wenn sich diese Seite nicht automatisch neu lädt, lade die Seite manuell neu, um das hochgeladene Backup zu sehen.");
                    location.reload();
                } else {
                    messageBox.setStatus(ProgressMessageBox.STATUS_FAIL);
                    messageBox.setMessage("Es ist ein Fehler aufgetreten! Möglicherweise ist die Datei keine Notendatei.");
                    console.log(this.responseText);
                }
            } else if(this.readyState == 4) {
                preventAppClosing = false;
                //error
                messageBox.setStatus(ProgressMessageBox.STATUS_FAIL);
                messageBox.setMessage("Es ist ein Fehler aufgetreten! Kontaktiere den Administrator.");
            }
        }
        var messageBox = new ProgressMessageBox("restoring-backup-modal", "Backup einlesen", "Lese das Backup ein...", "Schließe nicht das Fenster oder lade die Seite neu, während das Backup eingelesen wird!", true);
        messageBox.show();

        preventAppClosing = true;
        httpRequest.send(formData);
    }
}

function undoBackupRestore(){
    var httpRequest = new XMLHttpRequest();
    httpRequest.open("POST", "backup-file.php?action=undo");
    httpRequest.onreadystatechange = function(){
        if (this.readyState == 4 && this.status == 200) {
            //success
            preventAppClosing = false;
            if(this.responseText == "success"){
                messageBox.setStatus(ProgressMessageBox.STATUS_SUCCESS);
                messageBox.setMessage("Es wurde erfolgreich zum letzten Backup zurückgekehrt.");
                location.reload();
            } else {
                messageBox.setStatus(ProgressMessageBox.STATUS_FAIL);
                messageBox.setMessage("Es ist ein Fehler aufgetreten! Möglicherweise gibt es gar kein altes Backup.");
                console.log(this.responseText);
            }
        } else if(this.readyState == 4) {
            preventAppClosing = false;
            //error
            messageBox.setStatus(ProgressMessageBox.STATUS_FAIL);
            messageBox.setMessage("Es ist ein Fehler aufgetreten! Kontaktiere den Administrator.");
        }
    }
    var messageBox = new ProgressMessageBox("undo-backup-modal", "Backup rückgängig machen", "Kehre zum alten Backup zurück...", "Schließe nicht das Fenster oder lade die Seite neu, während zum alten Backup zurückgekehrt wird!", true);
    messageBox.show();

    preventAppClosing = true;
    httpRequest.send();
}