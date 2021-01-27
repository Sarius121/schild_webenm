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

/*function ClassTeacherTable(json){
    CustomEditableGrid.call(this, "ClassTeacherTable", json);
}

ClassTeacherTable.prototype = Object.create(CustomEditableGrid.prototype);
ClassTeacherTable.prototype.constructor = ClassTeacherTable;

ClassTeacherTable.prototype.renderGrid = function(){
    this.call(this, "classTeacherTable", "classTeacherGrid");
}

ClassTeacherTable.prototype.onTableRendered = function (){
    $("#classTeacherTable tr").dblclick(this._onRowDoubleClicked);

    const that = this;
    $('#btn-ct-previous').addEventListener("click", (event) => {that._changeSelectedUserRelative(-1)});
    $('#btn-ct-next').addEventListener("click", (event) => {that._changeSelectedUserRelative(1)});
}

ClassTeacherTable.prototype.onRowDoubleClicked = function(event){
    //get row
    row = event.currentTarget;
    this._activeRow = parseInt(row.id.split("_")[1]);

    //show grade selection modal
    $('#class-teacher-modal').modal();

    this._changeSelectedUser(this._activeCTRow);
}

ClassTeacherTable.prototype.changeSelectedUser = function(rowID){
    $("#btn-ct-previous").prop('disabled', false);
    $("#btn-ct-next").prop('disabled', false);
    if(rowID == 0){
        $("#btn-ct-previous").prop('disabled', true);
    }
    if(rowID == this._tableLength){
        $("#btn-ct-next").prop('disabled', true);
    }

    name = $("#" + this._tableID + "_" + rowID + " .editablegrid-Name").html() + " (" + $("#ClassTeacherTable_" + rowID + " .editablegrid-Klasse").html() + ")";
    asv = $("#" + this._tableID + "_" + rowID + " .editablegrid-ASV").html();
    aue = $("#" + this._tableID + "_" + rowID + " .editablegrid-AuE").html();
    zb = $("#" + this._tableID + "_" + rowID + " .editablegrid-ZeugnisBem").html();

    $("#ct-selected-name").html(name);
    $("#textarea-asv").val(asv);
    $("#textarea-aue").val(aue);
    $("#textarea-zb").val(zb);

    this._activeRow = rowID;
}

ClassTeacherTable.prototype.changeSelectedUserRelative = function(relRowID){
    this._changeCTSelectedUser(this._activeRow + relRowID);
}

ClassTeacherTable.prototype.renderPhrasesTable = function(json){
    this.phrasesTable = new PhrasesTable(json);
    this._phrasesTable.renderGrid();
}*/

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
        var activeCTRow = this.classTeacherTable.activeRow;

        var asv = $("#textarea-asv").val();
        var aue = $("#textarea-aue").val();
        var zb = $("#textarea-zb").val();
    
        /*$("#ClassTeacherTable_" + rowID + " .editablegrid-ASV").html(asv);
        $("#ClassTeacherTable_" + rowID + " .editablegrid-AuE").html(aue);
        $("#ClassTeacherTable_" + rowID + " .editablegrid-ZeugnisBem").html(zb);*/
        $('#class-teacher-modal').modal("hide");
        this.classTeacherTable.changeTextCell(activeCTRow, ".editablegrid-ASV", asv);
        this.classTeacherTable.changeTextCell(activeCTRow, ".editablegrid-AuE", aue);
        this.classTeacherTable.changeTextCell(activeCTRow, ".editablegrid-ZeugnisBem", zb);
        $('#class-teacher-modal').modal("show");
    
        var hasASV = (asv.length > 0);
        var hasAUE = (aue.length > 0);
        var hasZB = (zb.length > 0);
    
        this.classTeacherTable.changeCheckboxCell(activeCTRow, ".editablegrid-hasASV", hasASV);
        this.classTeacherTable.changeCheckboxCell(activeCTRow, ".editablegrid-hasAuE", hasAUE);
        this.classTeacherTable.changeCheckboxCell(activeCTRow, ".editablegrid-hasZeugnisBem", hasZB);
    }
}

/*
function PhrasesTable(json){
    PhrasesTable.call(this, "PhrasesTable", json);
}

PhrasesTable.prototype = Object.create(CustomEditableGrid.prototype);
PhrasesTable.prototype.constructor = PhrasesTable;

PhrasesTable.prototype.onTableRendered = function (){
    this._filterPhrasesTable("ASV", document.getElementsByName("ASV")[0]);
    $("#phrasesTable tbody tr").dblclick(this._onRowDoubleClicked);

    //events
    const that = this;
    $('#textarea-asv').addEventListener("focus", (event) => {that._filterPhrasesTable('ASV', event.currentTarget)});
    $('#textarea-aue').addEventListener("focus", (event) => {that._filterPhrasesTable('AUE', event.currentTarget)});
    $('#textarea-zb').addEventListener("focus", (event) => {that._filterPhrasesTable('ZB', event.currentTarget)});

    $("#class-teacher-head textarea").addEventListener("onchange", this._onPhrasesChanged);
}

PhrasesTable.prototype.renderGrid = function(){
    this.call(this, "phrasesTable", "phrasesGrid");
}

PhrasesTable.prototype.filterPhrasesTable = function(filterGroup, origin = null){
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

PhrasesTable.prototype.onRowDoubleClicked = function(event){
    multipleVorname = !document.getElementById("multipleFirstnames").checked;
    
    text = $(event.currentTarget).find(".editablegrid-Floskeltext").first().html();

    firstname = $("#ClassTeacherTable_" + activeCTRow + " .editablegrid-Name").html().split(", ")[1];
    
    textarea = $("#class-teacher-head textarea.active").first();
    currentText = textarea.val();
    
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

    this._onPhrasesChanged();
}

PhrasesTable.prototype.onPhrasesChanged = function(classTeacherTable){
    asv = $("#textarea-asv").val();
    aue = $("#textarea-aue").val();
    zb = $("#textarea-zb").val();

    /*$("#ClassTeacherTable_" + rowID + " .editablegrid-ASV").html(asv);
    $("#ClassTeacherTable_" + rowID + " .editablegrid-AuE").html(aue);
    $("#ClassTeacherTable_" + rowID + " .editablegrid-ZeugnisBem").html(zb);*//*
    $('#class-teacher-modal').modal("hide");
    classTeacherTable._changeTextCell(activeCTRow, ".editablegrid-ASV", asv);
    classTeacherTable._changeTextCell(activeCTRow, ".editablegrid-AuE", aue);
    classTeacherTable._changeTextCell(activeCTRow, ".editablegrid-ZeugnisBem", zb);
    $('#class-teacher-modal').modal("show");


    hasASV = (asv.length > 0);
    hasAUE = (aue.length > 0);
    hasZB = (zb.length > 0);

    classTeacherTable._changeCheckboxCell(activeCTRow, ".editablegrid-hasASV", hasASV);
    classTeacherTable._changeCheckboxCell(activeCTRow, ".editablegrid-hasAuE", hasAUE);
    classTeacherTable._changeCheckboxCell(activeCTRow, ".editablegrid-hasZeugnisBem", hasZB);
}*/


/*function onClassTeacherTableLoaded(){
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
    $("#textarea-asv").val(asv);
    $("#textarea-aue").val(aue);
    $("#textarea-zb").val(zb);

    activeCTRow = rowID;
}

function onPhraseSelected(event){
    multipleVorname = !document.getElementById("multipleFirstnames").checked;

    text = $(event.currentTarget).find(".editablegrid-Floskeltext").first().html();

    firstname = $("#ClassTeacherTable_" + activeCTRow + " .editablegrid-Name").html().split(", ")[1];
    
    textarea = $("#class-teacher-head textarea.active").first();
    currentText = textarea.val();
    
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

    onPhrasesChanged();
}

function onPhrasesChanged(){
    asv = $("#textarea-asv").val();
    aue = $("#textarea-aue").val();
    zb = $("#textarea-zb").val();

    /*$("#ClassTeacherTable_" + rowID + " .editablegrid-ASV").html(asv);
    $("#ClassTeacherTable_" + rowID + " .editablegrid-AuE").html(aue);
    $("#ClassTeacherTable_" + rowID + " .editablegrid-ZeugnisBem").html(zb);*//*
    $('#class-teacher-modal').modal("hide");
    changeTextCell(activeCTRow, ".editablegrid-ASV", asv);
    changeTextCell(activeCTRow, ".editablegrid-AuE", aue);
    changeTextCell(activeCTRow, ".editablegrid-ZeugnisBem", zb);
    $('#class-teacher-modal').modal("show");


    hasASV = (asv.length > 0);
    hasAUE = (aue.length > 0);
    hasZB = (zb.length > 0);

    changeCheckboxCell(activeCTRow, ".editablegrid-hasASV", hasASV);
    changeCheckboxCell(activeCTRow, ".editablegrid-hasAuE", hasAUE);
    changeCheckboxCell(activeCTRow, ".editablegrid-hasZeugnisBem", hasZB);
}

function changeCheckboxCell(row, col, value){
    selector = "#ClassTeacherTable_" + row + " " + col + " input";
    $(selector).prop('checked', value);
}

function changeTextCell(row, col, value){
    focusCTCell(row, col);
    selector = "#ClassTeacherTable_" + row + " " + col + " input";
    $(selector).val(value);
    //TODO blur
}

function focusCTCell(row, col){
    //get row (activeRow is set in gradeTable.js)
    selector = "#ClassTeacherTable_" + row + " " + col;

    //simulate click
    $(selector).trigger('click');
}*/