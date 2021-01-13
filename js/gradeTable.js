window.addEventListener("load", function(event) {
    //$(".editablegrid-NotenKrz").click(onGradeCellClicked);
    //$("#gradeTable .editablegrid-NotenKrz").css("color", "red");
});

function onGradeTableLoaded(){
    $("#gradeTable .editablegrid-NotenKrz").dblclick(onGradeCellDoubleClicked);
}

activeRow = 1;

function onGradeCellDoubleClicked(event){
    //get row
    cell = event.currentTarget;
    activeRow = parseInt(cell.parentElement.id.split("_")[1]);

    //show grade selection modal
    $('#grades-modal').modal();
    $('#grades-modal').on('hidden.bs.modal', function (e) {
        focusCell(activeRow, ".editablegrid-NotenKrz");
    });
    
    cell.parentElement.classList.add("active");
}

function onGradeCellKeyPressed(event){
    cell = event.target;
    cell.style.setProperty("background-color", "red");
}

function focusCell(row, col){
    //get row (activeRow is set in gradeTable.js)
    selector = "#GradeTable_" + row + " " + col;

    //simulate click and set value of input
    $(selector).trigger('click');
}