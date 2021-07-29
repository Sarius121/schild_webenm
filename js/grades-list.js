window.addEventListener("load", function(event) {
    $(".modal-dialog").draggable({ cancel: ".modal-body, .modal-footer", containment: "html", scroll: false });
});

/*selectedGrade = "";

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

function filterGrades(filterType){
    console.log("filter");
    var rows = $("#grades-list .row");

    rows.each(function() {
        if($(this).hasClass("grid-header")){
            return;
        }
        var type = $(this).children().last().html();
        if(filterType == null || type == filterType){
            $(this).removeClass("hidden");
        } else {
            $(this).addClass("hidden");
        }
    });
}*/

class GradesModal{
    constructor(){
        const that = this;
        $("#grades-list .row").each(function() {
            if($(this).hasClass("grid-header")){
                return;
            }
            $(this).click((event) => {that.onGradesListRowClicked(event)});
            $(this).dblclick((event) => {that.onGradesListRowDoubleClicked(event)});
        });

        $("#grades-modal .modal-footer button").last().click(() => {that.onGradesListOKClicked()});
    }

    show(table, col){
        this.table = table;
        this.col = col;

        const tableID = this.table.tableID;

        $('#grades-modal').modal('show');
        $('#grades-modal').on('hide.bs.modal', function () {
            $('#' + tableID + ' .active').removeClass('active');
          });
    }

    onGradesListRowClicked(event){
        $("#grades-list .row").removeClass('selected');
        event.currentTarget.classList.add("selected");

        this.selectedGrade = event.currentTarget.children[1].innerHTML; //TODO check index
    }

    onGradesListRowDoubleClicked(event){
        this.selectedGrade = event.currentTarget.children[1].innerHTML; //TODO check index
        this.onGradesListOKClicked();
    }

    onGradesListOKClicked(){
        //hide modal
        $('#grades-modal').modal('hide');

        //set value of input
        this.table.changeTextCell(this.table.activeRow, this.col, this.selectedGrade);
    }

    filterGrades(filterType){
        var rows = $("#grades-list .row");

        rows.each(function() {
            if($(this).hasClass("grid-header")){
                return;
            }
            var type = $(this).children().last().html();
            if(filterType == null || type == filterType){
                $(this).removeClass("hidden");
            } else {
                $(this).addClass("hidden");
            }
        });
    }
}