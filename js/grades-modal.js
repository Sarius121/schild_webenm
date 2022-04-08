/**
 * this modal shows all valid grades for a specific cell and allows the user to select a grade instead of typing it
 * this modal is based on the bootstrap grid and not on the editable grid
 */
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