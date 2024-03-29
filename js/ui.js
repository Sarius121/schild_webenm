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
    initRequests();
    if (this.document.getElementById("admin-container") != null) {
        Array.from(document.getElementsByClassName("admin-button")).forEach(item => {
            item.addEventListener("click", onAdminPageButtonClicked)
        });
    } else if (this.document.getElementById("home-container") != null) {
        addUIEventListeners();
        initObjects();
    }
});

/**
 * is called when a button in the overview table is clicked
 * 
 * @param {Event} event 
 */
function onAdminPageButtonClicked(event) {
    var action = event.currentTarget.getAttribute("name");

    var data = new FormData();
    data.append("action", action);

    switch(action){
        case "save-changes-all":
            var messageBox = new ProgressMessageBox("save-changes-all", "Alle Änderungen speichern", "Die Änderungen aller offenen Dateien werden gesichert...", "Schließe nicht das Fenster oder lade die Seite neu, während die Änderungen gesichert werden!", true);
            break;
        case "save-changes":
            var messageBox = new ProgressMessageBox("save-changes", "Änderungen speichern", "Die Änderungen werden gesichert...", "Schließe nicht das Fenster oder lade die Seite neu, während die Änderungen gesichert werden!", true);
            var targetFile = event.currentTarget.parentElement.parentElement.children[2].textContent;
            data.append("target", targetFile)
            break;
        case "save-changes-all":
            var messageBox = new ProgressMessageBox("discard-changes-all", "Alle Änderungen verwerfen", "Die Änderungen aller offenen Dateien werden verworfen...", "Schließe nicht das Fenster oder lade die Seite neu, während die Änderungen verworfen werden!", true);
            break;
        case "discard-changes":
            var messageBox = new ProgressMessageBox("discard-changes", "Änderungen verwerfen", "Die Änderungen werden verworfen...", "Schließe nicht das Fenster oder lade die Seite neu, während die Änderungen verworfen werden!", true);
            var targetFile = event.currentTarget.parentElement.parentElement.children[2].textContent;
            data.append("target", targetFile)
            break;
        case "download-all":
            var messageBox = new ProgressMessageBox("download-changes", "Alle Dateien als ZIP runterladen", "Die Dateien werden zusammengefasst...", "Schließe nicht das Fenster oder lade die Seite neu, während der Download vorbereitet wird!", true);
            break;
        case "delete-archives":
            // not implemented yet
            return;
        default:
            return;
    }

    messageBox.show();
    preventAppClosing = true;

    requests.addRequestToQueue("POST", "admin-actions.php", data, function(result){
        if(result.code == 0){
            //success
            preventAppClosing = false;
            messageBox.setStatus(ProgressMessageBox.STATUS_SUCCESS);
            switch(action){
                case "save-changes-all":
                    messageBox.setMessage("Alle Änderungen wurden gesichert.");
                    break;
                case "save-changes":
                    messageBox.setMessage("Die Änderungen wurden gesichert.");
                    break;
                case "discard-changes-all":
                    messageBox.setMessage("Alle Änderungen wurden verworfen.");
                    break;    
                case "discard-changes":
                    messageBox.setMessage("Die Änderungen wurden verworfen.");
                    break;
                case "download-all":
                    if("download_token" in result){
                        messageBox.setMessage("Alle Dateien wurden zusammengefasst und werden jetzt runtergeladen.");
                        messageBox.hide();
                        window.open("download.php?token=" + result.download_token);
                    }
                    break;
                case "delete-archives":
                    // not implemented yet
                    break;
                default:
                    return;
            }
            messageBox.hide();
        } else {
            preventAppClosing = false;
            //error
            messageBox.setStatus(ProgressMessageBox.STATUS_FAIL);
            messageBox.setMessage("Es ist ein unbekannter Fehler aufgetreten!");
        }
    });
}


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
 * do action which belongs to the clicked menu item (specified by name attribute)
 * 
 * @param {Event} event
 */
function onMenuItemClicked(event){
    if(event.currentTarget.classList.contains("disabled")){
        return;
    }
    var action = event.currentTarget.getAttribute("name");

    switch(action){
        case "save-changes":
            saveChanges();
            break;
        case "fast-print":
            window.print();
            break;
        case "create-backup":
            var data = new FormData();
            data.append("action", "create");
            requests.addRequestToQueue("POST", "inactive-actions.php", data, function(result){
                if("download_token" in result){
                    window.open("download.php?token=" + result.download_token);
                }
            });
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
            var id = $("#data-container .active").first().attr("id");
            $("#filter-modal .filter-group").toggleClass("hidden", true);
            $("#" + id.replace("data", "filter")).toggleClass("hidden", false);

            var dataName = $("#nav-data .active").first().text();
            $("#filter-modal .modal-title").text(dataName + " filtern");
            $("#filter-modal").modal("show");
            break;
        case "delete-filter":
            var id = $("#data-container .active").first().attr("id");
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
        case "documentation":
            window.open('doc/webENM Benutzerdokumentation.pdf', '_blank');
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
function onRestoreBackupFileSelected(event){
    var files = event.currentTarget.files;
    if(files.length > 0){
        var formData = new FormData();
        formData.append("backupFile", files[0]);
        formData.append("action", "restore");

        var messageBox = new ProgressMessageBox("restoring-backup-modal", "Backup einlesen", "Lese das Backup ein...", "Schließe nicht das Fenster oder lade die Seite neu, während das Backup eingelesen wird!", true);
        messageBox.show();
        preventAppClosing = true;

        //send file
        requests.addRequestToQueue("POST", "inactive-actions.php", formData, function(result){
            if(result.code == 0){
                preventAppClosing = false;
                messageBox.setStatus(ProgressMessageBox.STATUS_SUCCESS);
                messageBox.setMessage("Das Backup wurde erfolgreich hochgeladen. Wenn sich diese Seite nicht automatisch neu lädt, lade die Seite manuell neu, um das hochgeladene Backup zu sehen.");
                location.reload();
            } else {
                preventAppClosing = false;
                //error
                messageBox.setStatus(ProgressMessageBox.STATUS_FAIL);
                if(result.code == 50){
                    messageBox.setMessage("Es ist ein Fehler aufgetreten! Möglicherweise ist die Datei keine Notendatei.");
                } else {
                    messageBox.setMessage("Es ist ein unbekannter Fehler aufgetreten!");
                }
            }
        });
    }
}

/**
 * is called when the menu item undo backup is clicked
 * try to undo last backup
 */
function undoBackupRestore(){
    var messageBox = new ProgressMessageBox("undo-backup-modal", "Backup rückgängig machen", "Kehre zum alten Backup zurück...", "Schließe nicht das Fenster oder lade die Seite neu, während zum alten Backup zurückgekehrt wird!", true);
    messageBox.show();

    preventAppClosing = true;

    var data = new FormData();
    data.append("action", "undo");

    requests.addRequestToQueue("POST", "inactive-actions.php", data, function(result){
        if(result.code == 0){
            //success
            preventAppClosing = false;
            messageBox.setStatus(ProgressMessageBox.STATUS_SUCCESS);
            messageBox.setMessage("Es wurde erfolgreich zum letzten Backup zurückgekehrt.");
            location.reload();
        } else {
            preventAppClosing = false;
            //error
            messageBox.setStatus(ProgressMessageBox.STATUS_FAIL);
            if(result.code == 50){
                messageBox.setMessage("Es ist ein Fehler aufgetreten! Möglicherweise gibt es gar kein altes Backup.");
            } else {
                messageBox.setMessage("Es ist ein unbekannter Fehler aufgetreten!");
            }
        }
    });
}

/**
 * is called when the menu item save is clicked
 * saves the current changes in the grade file to the source file
 */
function saveChanges(){
    var messageBox = new ProgressMessageBox("save-changes", "Änderungen speichern", "Die Änderungen werden gesichert...", "Schließe nicht das Fenster oder lade die Seite neu, während die Änderungen gesichert werden! Übrigens: Die Änderungen werden auch automatisch beim Abmelden gesichert.", true);
    messageBox.show();

    preventAppClosing = true;

    var data = new FormData();
    data.append("action", "save-changes");

    requests.addRequestToQueue("POST", "inactive-actions.php", data, function(result){
        if(result.code == 0){
            //success
            preventAppClosing = false;
            messageBox.setStatus(ProgressMessageBox.STATUS_SUCCESS);
            messageBox.setMessage("Die Änderungen wurden gesichert.");
            messageBox.hide();
        } else {
            preventAppClosing = false;
            //error
            messageBox.setStatus(ProgressMessageBox.STATUS_FAIL);
            messageBox.setMessage("Es ist ein unbekannter Fehler aufgetreten!");
        }
    });
}

/**
 * is called when the filter modal is submitted
 * filters the current data table by the selected filters
 */
function filterDataTable(){
    var id = $("#data-container .active").first().attr("id");
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
    var id = $("#data-container .active").first().attr("id");
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
 * add event listener to element only if it's not null
 * 
 * @param {HTMLElement} element element to which the listener should be added
 * @param {String} type listener type
 * @param {EventListenerOrEventListenerObject} listener method to call if event is triggered
 */
function addEventListenerIfPresent(element, type, listener) {
    if (element != null) {
        element.addEventListener(type, listener);
    }
}

function addUIEventListeners(){
    Array.from(document.getElementsByClassName("nav-menu-button")).forEach(item => {
        item.addEventListener("click", onMenuItemClicked)
    });
    addEventListenerIfPresent(document.getElementById("backupFile"), "change", onRestoreBackupFileSelected);
    Array.from(document.getElementsByClassName("nav-data-tabs-button")).forEach(item => {
        item.addEventListener('shown.bs.tab', function (event) {
            var data = event.currentTarget.getAttribute("name");
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
        });
    });
    addEventListenerIfPresent(document.getElementById("filter-data-table-button"), "click", filterDataTable);
}

function initRequests() {
    var token = document.getElementById("csrf_token");
    if (token == null) {
        // probably not on home-page
        return;
    }
    requests = new Requests(token.innerText);
    token.remove();
}

function initObjects(){
    gradesModal = new GradesModal();

    var tablesToFetch = ["GradeTable", "Grades"];
    if(document.getElementById("tab-class-teacher")){
        tablesToFetch.push("ClassTeacherTable");
        tablesToFetch.push("Phrases");
    }
    if(document.getElementById("tab-exams")){
        tablesToFetch.push("ExamsTable");
    }

    var postData = new FormData();
    postData.append("tables", JSON.stringify(tablesToFetch));

    requests.addRequestToQueue("POST", "fetch-data.php", postData, function(result){
        if(result.code == 0){
            if("GradeTable" in result.data && "Grades" in result.data){
                gradeTable = new GradeTable(requests, result.data.GradeTable, result.data.Grades);
                gradeTable.renderGrid();
            }
            if("ClassTeacherTable" in result.data && "Phrases" in result.data){
                classTeacherTable = new ClassTeacherTable(requests, result.data.ClassTeacherTable);
                classTeacherTable.renderGrid();
                classTeacherTable.renderPhrasesTable(result.data.Phrases);
            }
            if("ExamsTable" in result.data && "Grades" in result.data){
                examsTable = new ExamsTable(requests, result.data.ExamsTable, result.data.Grades);
                examsTable.renderGrid();
            }
        }
    });
}