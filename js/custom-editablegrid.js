class CustomEditableGrid{

    constructor(name, json){
        this.name = name;
        this.editableGrid = new EditableGrid(name, {editmode: "static"});
        this.editableGrid.load(json);
    }

    renderGrid(tableID, gridID){
        this.tableID = tableID;
        this.gridID = gridID;
        this.editableGrid.renderGrid(tableID, gridID);

        this.tableLength = $("#" + this.tableID + " tbody tr").last().attr('id').split("_")[1];

        this.onTableRendered();
    }

    onTableRendered(){

    }

    changeCheckboxCell(row, col, value){
        var selector = "#" + this.tableID + "_" + row + " ." + col + " input";
        $(selector).prop('checked', value);
    }
    
    changeTextCell(row, col, value){
        //get row
        var colSelector = "#" + this.tableID + "_" + row + " ." + col;
    
        //display cols
        $(colSelector).addClass("show");


        this.focusCell(row, col);
        var selector = "#" + this.tableID + "_" + row + " ." + col + " input";
        $(selector).val(value);

        //hide cols
        $(colSelector).removeClass("show");
    }
    
    focusCell(row, col){
        //get row (activeRow is set in gradeTable.js)
        var selector = "#" + this.tableID + "_" + row + " ." + col;
    
        //simulate click
        $(selector).trigger('click');
    }
}