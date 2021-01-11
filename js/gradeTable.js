window.addEventListener("load", function(event) {
    //$(".editablegrid-NotenKrz").click(onGradeCellClicked);
    //$("#gradeTable .editablegrid-NotenKrz").css("color", "red");
});

function onTableLoaded(){
    $("#gradeTable .editablegrid-NotenKrz").click(onGradeCellClicked);

    //$("#gradeTable .editablegrid-NotenKrz").click(onEditableCellClicked);
    //$("#gradeTable .editablegrid-Warnung").click(onEditableCellClicked);
    $("#gradeTable .editablegrid-NotenKrz input").keydown(onGradeCellKeyPressed);
}

function onGradeCellClicked(event){
    cell = event.target;
    //cell.style.setProperty("background-color", "red");
}

function onGradeCellKeyPressed(event){
    cell = event.target;
    cell.style.setProperty("background-color", "red");
}

function onEditableCellClicked(event){
    cell = event.target;
    inputWidth = cell.children[0].outerWidth();
    cell.style.setProperty("width", inputWidth);
}