// specifies whether a warning should be displayed before closing or reloading the website
preventAppClosing = false;

/**
 * prevent app from closing if there are unsaved changes
 * is currently triggered if a backup is uploaded or being undone
 * annotation regarding data changes: data changes are directly saved on the server (but not necessarily directly to the source file)
 */
window.onbeforeunload = function(){
    if(preventAppClosing){
        console.log("prevent");
        return "Ein Vorgang ist nicht beendet. Wenn du das Fenster schließt, wird der Vorgang abgebrochen.";
    } else {
        return;
    }
}

/**
 * make modals draggable at the modal header
 */
window.addEventListener("load", function(event) {
    $(".modal-dialog").draggable({ cancel: ".modal-body, .modal-footer", containment: "html", scroll: false });
});


/**
 * is called when menu tab button is clicked
 * changes the visible menu tab
 * 
 * @param {*} tabHeader tab button on which the user clicked
 * @param {*} tabName menu tab to show
 */
function onTabClicked(tabHeader, tabName){
    $("#menu-tab ul.body").removeClass('visible');
    document.getElementById(tabName).classList.add("visible");

    $("#menu-tab ul.header li").removeClass('active');
    $(tabHeader).addClass('active');
}

/**
 * is called when a menu item is clicked
 * do action which belongs to the clicked menu item
 * 
 * @param {*} item clicked menu item
 * @param {*} action action to do
 */
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
        case "sort-Fach-Name":
            sortCurrentTable(["FachBez", "Name"]);
            break;
        case "sort-Name-Fach":
            sortCurrentTable(["Name", "FachBez"]);
            break;
        case "sort-Klasse-Name":
            sortCurrentTable(["Klasse", "Name"]);
            break;
        case "sort-Klasse-Fach":
            sortCurrentTable(["Klasse", "FachBez"]);
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

/**
 * is called when a local backup file was selected
 * uploads the backup and tries to restore it
 * 
 * @param {*} files selected backup files
 */
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

/**
 * is called when the menu item undo backup is clicked
 * try to undo last backup
 */
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

/**
 * is called when the menu item save is clicked
 * saves the current changes in the grade file to the source file
 */
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

/**
 * is called when the filter modal is submitted
 * filters the current data table by the selected filters
 */
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

/**
 * is called when the menu item sort is clicked
 * 
 * @param {*} columns columns to sort by
 */
function sortCurrentTable(columns = []){
    var id = $("#data-container .visible").first().attr("id");
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
            if((index = columns.indexOf("FachBez")) != -1){
                columns[index] = "FachKrz";
            }
            break;
        default:
            return;
    }
    table.sortTable(columns);
}

/**
 * is called when tab navigation button was clicked
 * change visible data tab
 * 
 * @param {*} btn button on which the user clicked
 * @param {*} data data tab to show
 */
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