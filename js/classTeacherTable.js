function onClassTeacherTableLoaded(){
    $("#classTeacherTable tr").dblclick(onClassTeacherRowDoubleClicked);
}

activeCTRow = 1;

function onClassTeacherRowDoubleClicked(event){
    //get row
    row = event.currentTarget;
    activeCTRow = parseInt(row.id.split("_")[1]);

    //show grade selection modal
    $('#class-teacher-modal').modal();
    
    row.classList.add("active");
}

function onPhrasesTableLoaded(){
    filterPhrasesTable("ASV", document.getElementsByName("ASV")[0]);
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