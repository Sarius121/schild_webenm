window.addEventListener("load", function(event) {
    $(".modal-dialog").draggable({ cancel: ".modal-body", containment: "html", scroll: false });
});

selectedGrade = "";

function onGradesListRowClicked(row, grade){
    $("#grades-list .row").removeClass('selected');
    row.classList.add("selected");

    selectedGrade = grade;
}

function onGradesListRowDoubleClicked(grade){
    selectedGrade = grade;
    onGradesListOKClicked();
}

function onGradesListOKClicked(){
    //hide modal
    $('gradeTable .active').removeClass('active');
    $('#grades-modal').modal('hide');

    //get row (activeRow is set in gradeTable.js)
    row = activeRow;

    //set value of input
    //focusCell(row, ".editablegrid-NotenKrz") //focus is already set by modal.onhide
    $("#gradeTable input").val(selectedGrade);
    
}