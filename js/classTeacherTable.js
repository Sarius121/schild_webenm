/**
 * ClassTeacherTable
 * @constructor
 * @class ClassTeacherTable Class
 */
class ClassTeacherTable extends CustomEditableGrid{

    constructor(json){
        super( "ClassTeacherTable", json);
    }

    renderGrid(){
        super.renderGrid("classTeacherTable", "classTeacherGrid");
    }
    
    onTableRendered(){
        const that = this;
        $("#classTeacherTable tr").dblclick((event) => {that.onRowDoubleClicked(event)});
    
        document.getElementById('btn-ct-previous').addEventListener("click", (event) => {that.changeSelectedUserRelative(-1)});
        document.getElementById('btn-ct-next').addEventListener("click", (event) => {that.changeSelectedUserRelative(1)});

        //dblclick on checkbox should be handled but click not because it shouldn't be editable (disabled -> no dblclick event)
        $('#classTeacherTable .boolean input').attr("onclick", "return false;")
    }
    
    onRowDoubleClicked(event){
        //get row
        var row = event.currentTarget;
        this.activeRow = parseInt(row.id.split("_")[1]);
    
        //show grade selection modal
        $('#class-teacher-modal').modal();
    
        this.changeSelectedUser(this.activeRow);
    }
    
    changeSelectedUser(rowID){
        $("#btn-ct-previous").prop('disabled', false);
        $("#btn-ct-next").prop('disabled', false);
        if(rowID == 0){
            $("#btn-ct-previous").prop('disabled', true);
        }
        if(rowID == this.tableLength){
            $("#btn-ct-next").prop('disabled', true);
        }
    
        var name = $("#" + this.tableID + "_" + rowID + " .editablegrid-Name").html() + " (" + $("#ClassTeacherTable_" + rowID + " .editablegrid-Klasse").html() + ")";
        var asv = $("#" + this.tableID + "_" + rowID + " .editablegrid-ASV").html();
        var aue = $("#" + this.tableID + "_" + rowID + " .editablegrid-AuE").html();
        var zb = $("#" + this.tableID + "_" + rowID + " .editablegrid-ZeugnisBem").html();
    
        $("#ct-selected-name").html(name);
        $("#textarea-asv").val(asv);
        $("#textarea-aue").val(aue);
        $("#textarea-zb").val(zb);
    
        this.activeRow = rowID;
    }
    
    changeSelectedUserRelative(relRowID){
        this.changeSelectedUser(this.activeRow + relRowID);
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
            item.addEventListener("onchange", () => {that.onPhrasesChanged()});
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
        
        var text = $(event.currentTarget).find(".editablegrid-Floskeltext").first().html();
    
        var firstname = $("#ClassTeacherTable_" + this.classTeacherTable.activeRow + " .editablegrid-Name").html().split(", ")[1];
        
        var textarea = $("#class-teacher-head textarea.active").first();
        var currentText = textarea.val();
        
        if(multipleVorname || !currentText.includes(firstname)){
            text = text.replaceAll('$Vorname$', firstname);
        } else {
            text = text.replaceAll('$Vorname$', 'Er/Sie'); //TODO Geschlecht
        }
        text = text.replaceAll('$Anrede$', 'Ihre/Seine'); //TODO Geschlecht
    
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