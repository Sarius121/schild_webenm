/**
 * ClassTeacherTable
 * @constructor
 * @class ClassTeacherTable Class
 */
class ClassTeacherTable extends CustomEditableGrid{

    constructor(json){
        super( "ClassTeacherTable", json, ["Klasse"]);

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

        //create auto-complete filters
        $("#filter-class-teacher datalist").each(function(){
            var col = this.id.replace("filter-class-teacher-", "").replace("-options", "");
            that.possibleFilters[col].forEach(item => {
                var option = document.createElement("option");
                option.setAttribute("value", item);
                this.appendChild(option);
            });
        });
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
        $('#class-teacher-modal').modal('show');
    
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

    autoCompleteBox = new AutoCompleteBox("class-teacher-head", "");

    constructor(json, classTeacherTable){
        super("PhrasesTable", json, ["Floskelgruppe"]);
        this.classTeacherTable = classTeacherTable;

        const that = this;
        this.possibleFilters["Floskelgruppe"].forEach(item => {
            var phraseGroupFilter = document.createElement("li");
            var html = "";
            html = '<label class="form-check-label dropdown-item" for="phrasesFilterCheck' + item + '"><input class="form-check-input" type="checkbox" id="phrasesFilterCheck' + item + '" checked><span>';
            html += item;
            html += '</span></label>';
            phraseGroupFilter.innerHTML = html;
            document.getElementById("phraseFilterList").appendChild(phraseGroupFilter);
            $(phraseGroupFilter).find("input").change(function(){
                var group = $(this).attr("id").replace("phrasesFilterCheck", "");
                if(this.checked){
                    that.currentFilter[0].filter.push(group);
                    that.filterPhrasesTable(that.currentFilter[0].filter);
                } else {
                    var index = that.currentFilter[0].filter.indexOf(group);
                    that.currentFilter[0].filter.splice(index, 1);
                    that.filterPhrasesTable(that.currentFilter[0].filter);
                }
            });
        });

        document.getElementById("textarea-asv").addEventListener("focus", (event) => {that.filterPhrasesTable(['ASV'], event.currentTarget)});
        document.getElementById('textarea-aue').addEventListener("focus", (event) => {that.filterPhrasesTable(['AUE'], event.currentTarget)});
        document.getElementById('textarea-zb').addEventListener("focus", (event) => {that.filterPhrasesTable(['ZB'], event.currentTarget)});
    
        document.querySelectorAll("#class-teacher-head textarea").forEach(item => {
            item.addEventListener("change", () => {
                that.onPhrasesChanged();});
            item.addEventListener("keyup", (event) => {
                if(this.autoCompleteBox.active == false){
                    if(event.key == "#"){
                        console.log("Hashtag");
                        var pos = getCaretCoordinates(event.currentTarget, event.currentTarget.selectionEnd);
                        var parent = document.getElementById("class-teacher-head");
                        var boundariesTop = event.currentTarget.getBoundingClientRect().top - parent.getBoundingClientRect().top;
                        var boundariesLeft = event.currentTarget.getBoundingClientRect().left - parent.getBoundingClientRect().left;
                        this.autoCompleteBox.startAutoComplete(boundariesLeft + pos["left"], boundariesTop + pos["top"] - event.currentTarget.scrollTop);
                        var autoCompleteStart = event.currentTarget.selectionEnd - 1;
                        var textarea = event.currentTarget;
                        this.autoCompleteBox.committedListener = function(phrase){
                            if(phrase != null){
                                var autoCompleteEnd = textarea.selectionEnd;
                                var content = textarea.value;
                                textarea.value = content.slice(0, autoCompleteStart) + phrase + content.slice(autoCompleteEnd);
                            }
                        };
                    }
                } else {
                    if(/[a-zA-Z0-9]/.test(String.fromCharCode(event.keyCode))){
                        var pos = getCaretCoordinates(event.currentTarget, event.currentTarget.selectionEnd);
                        var parent = document.getElementById("class-teacher-head");
                        var boundariesTop = event.currentTarget.getBoundingClientRect().top - parent.getBoundingClientRect().top;
                        var boundariesLeft = event.currentTarget.getBoundingClientRect().left - parent.getBoundingClientRect().left;
                        this.autoCompleteBox.pushChange(event.key, boundariesLeft + pos["left"], boundariesTop + pos["top"] - event.currentTarget.scrollTop);
                    } else if(event.key != "Shift" && event.key != "ArrowUp" && event.key != "ArrowDown"){
                        this.autoCompleteBox.cancel();
                    }
                }
            });
            item.addEventListener("keydown", (event) => {
                if(this.autoCompleteBox.active){
                    if(event.key == "ArrowUp"){
                        this.autoCompleteBox.selectItem(this.autoCompleteBox.selectedItem - 1);
                        event.preventDefault();
                    } else if(event.key == "ArrowDown"){
                        this.autoCompleteBox.selectItem(this.autoCompleteBox.selectedItem + 1);
                        event.preventDefault();
                    } else if(event.key == "Enter"){
                        this.autoCompleteBox.commitAutoComplete();
                        event.preventDefault();
                    }
                }
            });
            item.addEventListener("mousedown", (event) => {
                if(this.autoCompleteBox.active){
                    this.autoCompleteBox.cancel();
                }
            });
            item.addEventListener("scroll", (event) => {
                if(this.autoCompleteBox.active){
                    var pos = getCaretCoordinates(event.currentTarget, event.currentTarget.selectionEnd);
                    var parent = document.getElementById("class-teacher-head");
                    var boundariesTop = event.currentTarget.getBoundingClientRect().top - parent.getBoundingClientRect().top;
                    var boundariesLeft = event.currentTarget.getBoundingClientRect().left - parent.getBoundingClientRect().left;
                    var textareaHeight = event.currentTarget.offsetHeight;
                    if(pos["top"] > textareaHeight || (pos["top"] - event.currentTarget.scrollTop) < 0){
                        //cursor is not visible at the moment -> cancel
                        this.autoCompleteBox.cancel();
                    } else {
                        this.autoCompleteBox.pushChange("", boundariesLeft + pos["left"], boundariesTop + pos["top"] - event.currentTarget.scrollTop);
                    }
                }
            });

          });
    }

    onTableRendered(){
        this.filterPhrasesTable("ASV", document.getElementsByName("ASV")[0]);
    
        //events
        const that = this;
        $("#phrasesTable tbody tr").dblclick((event) => {that.onRowDoubleClicked(event)});

        
    }
    
    renderGrid(){
        super.renderGrid("phrasesTable", "phrasesGrid");
    }
    
    filterPhrasesTable(filterGroups, origin = null){
        $("#phraseFilterList input").each(function() {
            var group = $(this).attr("id").replace("phrasesFilterCheck", "");
            if(filterGroups.includes(group)){
                $(this).prop("checked", true);
            } else {
                $(this).prop("checked", false);
            }
        });
    
        if(origin != null){
            $("#class-teacher-head textarea").removeClass("active");
            origin.classList.add("active");
        }

        this.filterTable();
        this.filterTable("Floskelgruppe", filterGroups);
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

class AutoCompleteBox {

    active = false;
    selectedItem = 0;
    committedListener = function(phrase){};

    constructor(parentId, columnSelector){
        this.columnSelector = columnSelector;
        this.parentId = parentId;
    }

    startAutoComplete(posX, posY){
        this.autoCompleteBox = document.createElement("div");
        this.autoCompleteBox.className = "container auto-complete-box";

        var innerHTML = "";
        var itemCount = 0;

        $("#phrasesTable tbody tr").each(function(){
            if(!$(this).hasClass("hidden")){
                var code = $(this).find(".editablegrid-Kuerzel").first().text();
                var phrase = $(this).find(".editablegrid-Floskeltext").first().text();
                innerHTML += "<div class='row'><div class='col-auto'><span>" + code + "</span></div><div class='col'>" + phrase + "</div></div>";
                itemCount++;
            }
        });
        this.itemCount = itemCount;
        this.autoCompleteBox.innerHTML = innerHTML;
        this.autoCompleteBox.style.top = posY;
        this.autoCompleteBox.style.left = posX;
        const that = this;
        this.autoCompleteBox.addEventListener("click", (event) => {
            var target = $(event.target);
            var currentTarget = null;
            if(target.hasClass("row")){
                currentTarget = target;
            } else if(target.hasClass("col") || target.hasClass("col-auto")) {
                currentTarget = target.parent();
            }
            if(currentTarget != null){
                var index = currentTarget.parent().children().index(currentTarget);
                that.selectItem(index);
                that.commitAutoComplete();
            }
        });
        document.getElementById(this.parentId).appendChild(this.autoCompleteBox);

        this.content = "#";
        this.active = true;
        this.selectItem(0);
    }

    selectItem(index){
        if(0 <= index && index < this.itemCount){
            $(this.autoCompleteBox).children().toggleClass("selected", false);
            this.autoCompleteBox.children.item(index).classList.toggle("selected", true);
            this.selectedItem = index;

            this.autoCompleteBox.children.item(index).scrollIntoView(false);
        }
    }

    pushChange(character, posX, posY){
        this.autoCompleteBox.style.top = posY;
        this.autoCompleteBox.style.left = posX;

        if(character.length > 0){
            this.content += character.toUpperCase();

            var that = this;
            $(this.autoCompleteBox).children().each(function(){
                if(!$(this).find(".col-auto").first().text().startsWith(that.content)){
                    that.itemCount--;
                    if($(this).hasClass("selected")){
                        this.remove();
                        if(that.selectedItem == 0){
                            that.selectItem(0);
                        } else {
                            that.selectItem(that.selectedItem - 1);
                        }
                    } else {
                        this.remove();
                    }
                    if(that.itemCount == 0){
                        that.cancel();
                    }
                }
            });
        }
    }

    commitAutoComplete(){
        var phrase = null;
        if(this.itemCount > 0){
            phrase = $(this.autoCompleteBox.children.item(this.selectedItem)).find(".col").text();
        }
        this.remove();
        this.committedListener(phrase);
    }

    cancel(){
        this.remove();
    }

    remove(){
        this.autoCompleteBox.remove();
        this.active = false;
    }
}