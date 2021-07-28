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
        case "save-changes":
            saveChanges();
            break;
        case "create-backup":
            window.open("backup-file.php?action=create");
            break;
        case "restore-backup":
            document.getElementById("backupFile").click();
            break;
        case "undo-backup":
            undoBackupRestore();
            break;
        case "create-filter":
            var id = $("#data-container .visible").first().attr("id");
            $("#filter-modal .filter-group").toggleClass("hidden", true);
            $("#" + id.replace("data", "filter")).toggleClass("hidden", false);

            var dataName = $("#nav-data .active").first().text();
            $("#filter-modal .modal-title").text(dataName + " filtern");
            $("#filter-modal").modal("show");
            break;
        case "delete-filter":
            var id = $("#data-container .visible").first().attr("id");
            switch(id){
                case "data-grades":
                    gradeTable.filterTable();
                    break;
                case "data-class-teacher":
                    classTeacherTable.filterTable();
                    break;
                case "data-exams":
                    examsTable.filterTable();
                    break;
            }
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

function saveChanges(){
    var httpRequest = new XMLHttpRequest();
    httpRequest.open("POST", "inactive-actions.php?action=save-changes");
    httpRequest.onreadystatechange = function(){
        if (this.readyState == 4 && this.status == 200) {
            //success
            preventAppClosing = false;
            if(this.responseText == "success"){
                messageBox.setStatus(ProgressMessageBox.STATUS_SUCCESS);
                messageBox.setMessage("Die Änderungen wurden gesichert.");
                messageBox.hide();
            } else {
                messageBox.setStatus(ProgressMessageBox.STATUS_FAIL);
                messageBox.setMessage("Es ist ein unbekannter Fehler aufgetreten!");
                console.log(this.responseText);
            }
        } else if(this.readyState == 4) {
            preventAppClosing = false;
            //error
            messageBox.setStatus(ProgressMessageBox.STATUS_FAIL);
            messageBox.setMessage("Es ist ein unbekannter Fehler aufgetreten!");
        }
    }
    var messageBox = new ProgressMessageBox("save-changes", "Änderungen speichern", "Die Änderungen werden gesichert...", "Schließe nicht das Fenster oder lade die Seite neu, während die Änderungen gesichert werden! Übrigens: Die Änderungen werden auch automatisch beim Abmelden gesichert.", true);
    messageBox.show();

    preventAppClosing = true;
    httpRequest.send();
}

function filterDataTable(){
    var id = $("#data-container .visible").first().attr("id");
    var tablePrefix = id.replace("data-", "");
    var table = null;
    switch(id){
        case "data-grades":
            table = gradeTable;
            break;
        case "data-class-teacher":
            table = classTeacherTable;
            break;
        case "data-exams":
            table = examsTable;
            break;
        default:
            return;
    }
    //delete old filters
    table.filterTable();
    
    //filter table
    $("#filter-" + tablePrefix + " input").each(function(){
        var col = $(this).attr("id").replace("filter-" + tablePrefix + "-", "");
        var value = $(this).val();
        if(value == ""){
            return;
        }
        if(col == "MissingGrade"){
            if(this.checked){
                if(tablePrefix == "grades"){
                    table.filterTable("NotenKrz", [ "" ]);
                } else if(tablePrefix == "exams"){
                    table.filterTable("NoteAbschluss", [ "" ]);
                }
            }
        } else {
            table.filterTable(col, [ value ]);
        }
    });
    $("#filter-modal").modal("hide");
}