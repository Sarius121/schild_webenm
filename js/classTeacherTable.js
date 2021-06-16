/**
 * ClassTeacherTable
 * @constructor
 * @class ClassTeacherTable Class
 */
class ClassTeacherTable extends CustomEditableGrid{

    constructor(json){
        super( "ClassTeacherTable", json);

        /*var positiveNumberValidator = new CellValidator({ 
			isValid: function(value) {
                return (value == "" || parseInt(value) >= 0);
             }
		});

        this.addCellValidator("SumFehlstd", positiveNumberValidator);
        this.addCellValidator("SumFehlstdU", positiveNumberValidator);*/

        const that = this;
        document.getElementById('btn-ct-previous').addEventListener("click", (event) => {that.changeSelectedUserRelative(-1)});
        document.getElementById('btn-ct-next').addEventListener("click", (event) => {that.changeSelectedUserRelative(1)});
    }

    renderGrid(){
        super.renderGrid("classTeacherTable", "classTeacherGrid");
    }
    
    onTableRendered(){
        const that = this;
        $("#classTeacherTable tr").dblclick((event) => {that.onRowDoubleClicked(event)});

        //dblclick on checkbox should be handled but click not because it shouldn't be editable (disabled -> no dblclick event)
        $('#classTeacherTable .boolean input').attr("onclick", "return false;")
    }
    
    onRowDoubleClicked(event){
        //get row
        var row = event.currentTarget;
        this.activeRow = parseInt(row.id.split("_")[1]);
        this.activeRelativeRow = Array.prototype.indexOf.call(row.parentNode.children, row);
    
        //show grade selection modal
        $('#class-teacher-modal').modal();
    
        this.changeSelectedUser(this.activeRelativeRow);
    }
    
    changeSelectedUser(rowIndex){
        $("#btn-ct-previous").prop('disabled', false);
        $("#btn-ct-next").prop('disabled', false);
        if(rowIndex == 0){
            $("#btn-ct-previous").prop('disabled', true);
        }
        if(rowIndex == this.tableLength - 1){
            $("#btn-ct-next").prop('disabled', true);
        }

        var rowFullID = document.getElementById(this.tableID).getElementsByTagName("tbody")[0].children.item(rowIndex).id.split("_");
        var rowID = rowFullID[rowFullID.length - 1];
    
        var name = $("#" + this.tableID + "_" + rowID + " .editablegrid-Name").html() + " (" + $("#ClassTeacherTable_" + rowID + " .editablegrid-Klasse").html() + ")";
        var asv = $("#" + this.tableID + "_" + rowID + " .editablegrid-ASV").html();
        var aue = $("#" + this.tableID + "_" + rowID + " .editablegrid-AuE").html();
        var zb = $("#" + this.tableID + "_" + rowID + " .editablegrid-ZeugnisBem").html();
    
        $("#ct-selected-name").html(name);
        $("#textarea-asv").val(asv);
        $("#textarea-aue").val(aue);
        $("#textarea-zb").val(zb);
    
        this.activeRelativeRow = rowIndex;
        this.activeRow = rowID;
    }
    
    changeSelectedUserRelative(relRowID){
        this.changeSelectedUser(this.activeRelativeRow + relRowID);
    }
    
    renderPhrasesTable(json){
        this.phrasesTable = new PhrasesTable(json, this);
        this.phrasesTable.renderGrid();
    }
}

/**
 * PhrasesTable
 * @constructor
 * @class PhrasesTable Class
 */
class PhrasesTable extends CustomEditableGrid{

    constructor(json, classTeacherTable){
        super("PhrasesTable", json);
        this.classTeacherTable = classTeacherTable;
    }

    onTableRendered(){
        this.filterPhrasesTable("ASV", document.getElementsByName("ASV")[0]);
    
        //events
        const that = this;
        $("#phrasesTable tbody tr").dblclick((event) => {that.onRowDoubleClicked(event)});

        document.getElementById("textarea-asv").addEventListener("focus", (event) => {that.filterPhrasesTable('ASV', event.currentTarget)});
        document.getElementById('textarea-aue').addEventListener("focus", (event) => {that.filterPhrasesTable('AUE', event.currentTarget)});
        document.getElementById('textarea-zb').addEventListener("focus", (event) => {that.filterPhrasesTable('ZB', event.currentTarget)});
    
        document.querySelectorAll("#class-teacher-head textarea").forEach(item => {
            item.addEventListener("change", () => {
                that.onPhrasesChanged()});
          });
    }
    
    renderGrid(){
        super.renderGrid("phrasesTable", "phrasesGrid");
    }
    
    filterPhrasesTable(filterGroup, origin = null){
        var table = document.getElementById("phrasesTable");
        var rows = $("#phrasesTable tbody tr");
    
        if(origin != null){
            $("#class-teacher-head textarea").removeClass("active");
            origin.classList.add("active");
        }
    
        rows.each(function() {
            var group = $(this).find('.editablegrid-Floskelgruppe').html();
            if(group == filterGroup){
                $(this).css("visibility", "visible");
                $(this).css("display", "table-row");
            } else {
                $(this).css("visibility", "hidden");
                $(this).css("display", "none");
            }
        });
    }
    
    onRowDoubleClicked(event){
        var multipleVorname = !document.getElementById("multipleFirstnames").checked;
        
        var text = $(event.currentTarget).find(".editablegrid-Floskeltext").first().text();
    
        var firstname = $("#ClassTeacherTable_" + this.classTeacherTable.activeRow + " .editablegrid-Name").html().split(", ")[1];
        
        var textarea = $("#class-teacher-head textarea.active").first();
        var currentText = textarea.val();
        
        if(multipleVorname || !currentText.includes(firstname)){
            text = text.replaceAll('$Vorname$', firstname);
        } else {
            text = text.replaceAll('$Vorname$', 'Er/Sie'); //TODO Geschlecht
        }
        text = text.replaceAll('$Anrede$', 'Ihre/Seine'); //TODO Geschlecht

        //replace variables with options: e.g. &Klassensprecher%Klassensprecherin& -> Klassensprecher/Klassensprecherin
        var foundMatch = text.match(/&(\S*)%(\S*)&/);
        while(foundMatch != null){
            text = text.replace(foundMatch[0], foundMatch[1] + "/" + foundMatch[2]); //TODO Geschlecht -> first match is male, second female
            foundMatch = text.match(/&(\S*)%(\S*)&/);
        }
    
        if(currentText.length > 0){
            currentText += " ";
        }
        textarea.val(currentText + text);
    
        this.onPhrasesChanged();
    }
    
    onPhrasesChanged(){
        //TODO somewhere here is an error!
        var activeCTRow = this.classTeacherTable.activeRow;

        var asv = $("#textarea-asv").val();
        var aue = $("#textarea-aue").val();
        var zb = $("#textarea-zb").val();
    
        /*$("#ClassTeacherTable_" + rowID + " .editablegrid-ASV").html(asv);
        $("#ClassTeacherTable_" + rowID + " .editablegrid-AuE").html(aue);
        $("#ClassTeacherTable_" + rowID + " .editablegrid-ZeugnisBem").html(zb);*/
        $('#class-teacher-modal').modal("hide");
        this.classTeacherTable.changeTextCell(activeCTRow, "editablegrid-ASV", asv);
        this.classTeacherTable.changeTextCell(activeCTRow, "editablegrid-AuE", aue);
        this.classTeacherTable.changeTextCell(activeCTRow, "editablegrid-ZeugnisBem", zb);
        $('#class-teacher-modal').modal("show");
    
        var hasASV = (asv.length > 0);
        var hasAUE = (aue.length > 0);
        var hasZB = (zb.length > 0);
    
        this.classTeacherTable.changeCheckboxCell(activeCTRow, "editablegrid-hasASV", hasASV);
        this.classTeacherTable.changeCheckboxCell(activeCTRow, "editablegrid-hasAuE", hasAUE);
        this.classTeacherTable.changeCheckboxCell(activeCTRow, "editablegrid-hasZeugnisBem", hasZB);
    }
}