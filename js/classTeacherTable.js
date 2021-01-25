activeCTRow = 0;
cTTableLength = 0;

function onClassTeacherTableLoaded(){
    $("#classTeacherTable tr").dblclick(onClassTeacherRowDoubleClicked);
    cTTableLength = $("#ClassTeacherTable tbody tr").last().attr('id').split("_")[1];
}

function onClassTeacherRowDoubleClicked(event){
    //get row
    row = event.currentTarget;
    activeCTRow = parseInt(row.id.split("_")[1]);

    //show grade selection modal
    $('#class-teacher-modal').modal();
    
    //row.classList.add("active");

    changeCTSelectedUser(activeCTRow);
}

function onPhrasesTableLoaded(){
    filterPhrasesTable("ASV", document.getElementsByName("ASV")[0]);
    $("#phrasesTable tbody tr").dblclick(onPhraseSelected);
}

function filterPhrasesTable(filterGroup, origin = null){
    table = document.getElementById("phrasesTable")
    rows = $("#phrasesTable tbody tr");

    if(origin != null){
        $("#class-teacher-head textarea").removeClass("active");
        origin.classList.add("active");
    }

    rows.each(function() {
        group = $(this).find('.editablegrid-Floskelgruppe').html();
        if(group == filterGroup){
            $(this).css("visibility", "visible");
            $(this).css("display", "table-row");
        } else {
            $(this).css("visibility", "hidden");
            $(this).css("display", "none");
        }
    });
}

function changeCTSelectedUserRelative(relRowID){
    changeCTSelectedUser(activeCTRow + relRowID);
}

function changeCTSelectedUser(rowID){
    $("#btn-ct-previous").prop('disabled', false);
    $("#btn-ct-next").prop('disabled', false);
    if(rowID == 0){
        $("#btn-ct-previous").prop('disabled', true);
    }
    if(rowID == cTTableLength){
        $("#btn-ct-next").prop('disabled', true);
    }

    name = $("#ClassTeacherTable_" + rowID + " .editablegrid-Name").html() + " (" + $("#ClassTeacherTable_" + rowID + " .editablegrid-Klasse").html() + ")";
    asv = $("#ClassTeacherTable_" + rowID + " .editablegrid-ASV").html();
    aue = $("#ClassTeacherTable_" + rowID + " .editablegrid-AuE").html();
    zb = $("#ClassTeacherTable_" + rowID + " .editablegrid-ZeugnisBem").html();

    $("#ct-selected-name").html(name);
    $("#textarea-asv").html(asv);
    $("#textarea-aue").html(aue);
    $("#textarea-zb").html(zb);

    activeCTRow = rowID;
}

function onPhraseSelected(event){
    multipleVorname = !document.getElementById("multipleFirstnames").checked;

    text = $(event.currentTarget).find(".editablegrid-Floskeltext").first().html();

    firstname = $("#ClassTeacherTable_" + activeCTRow + " .editablegrid-Name").html().split(", ")[1];
    
    textarea = $("#class-teacher-head textarea.active").first();
    currentText = textarea.html();
    
    if(multipleVorname || !currentText.includes(firstname)){
        text = text.replaceAll('$Vorname$', firstname);
    } else {
        text = text.replaceAll('$Vorname$', 'Er/Sie'); //TODO Geschlecht
    }

    if(currentText.length > 0){
        currentText += " ";
    }
    textarea.html(currentText + text);
}