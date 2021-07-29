class CustomEditableGrid{

    currentFilter = [];
    possibleFilters = {};

    constructor(name, json, filterCols = []){
        this.name = name;
        this.editableGrid = new EditableGrid(name, {editmode: "static"});
        this.editableGrid.load(json);

        // override the function that will handle model changes
        this.editableGrid.modelChanged = function(rowIndex, columnIndex, oldValue, newValue, row) {
            var sessionID = document.cookie.match(/PHPSESSID=[^;]+/)[0].substring(10);
            
            var priKeyCol = this.columns[0]["name"];
            var priKey = this.data[rowIndex]["columns"][0];
            var col = this.columns[columnIndex]["name"];

            var postData = [{
                table : this.name,
                priKeyCol : priKeyCol,
                priKey : priKey,
                col : col,
                value : newValue,
            }];

            var post = "session_id=" + sessionID + "&data=" + encodeURIComponent(JSON.stringify(postData));
            //console.log(post);

            //TODO send
            var request = new XMLHttpRequest();
            //TODO look for errors request.addEventListener
            request.open("POST", "push-data.php");
            request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            request.onload = function(e){
                console.log(request.response);
            };
            request.send(post);
        }

        var that = this;

        this.editableGrid.tableRendered = function(){
            $("#" + name + " th").click(function(event) {
                if(event.target == event.currentTarget){
                    $(this).find("a").trigger("click");
                }
            });
            $("#" + name + " th a").attr("data-tooltip", "Sortieren");
            $("#" + name + " tbody input[type='checkbox']").toggleClass("form-check-input", true);

            //filter table after render
            that.currentFilter.forEach(function(item){
                that.filterTable(item.col, item.filter);
            });

            that.onTableRendered();
        }

        if(filterCols.length > 0){
            filterCols.forEach(function(col){
                that.possibleFilters[col] = [];
            });
            json.data.forEach(function(row){
                filterCols.forEach(function(col){
                    if(row.values[col] != undefined && row.values[col] != "" && !that.possibleFilters[col].includes(row.values[col])){
                        that.possibleFilters[col].push(row.values[col]);
                    }
                });
            });
        }

    }

    addCellValidator(col, validator){
        this.editableGrid.addCellValidator(col, validator);
    }

    renderGrid(tableID, gridID){
        this.tableID = tableID;
        this.gridID = gridID;

        this.editableGrid.renderGrid(tableID, gridID);

        this.tableLength = $("#" + this.tableID + " tbody tr").length;
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

        //apply changes by bluring input
        $(selector).blur();

        //hide cols
        $(colSelector).removeClass("show");
    }
    
    focusCell(row, col){
        //get row (activeRow is set in gradeTable.js)
        var selector = "#" + this.tableID + "_" + row + " ." + col;
    
        //simulate click
        $(selector).trigger('click');
    }

    filterTable(col = "all", filterStrings = "all"){
        if(col == "all" && typeof filterStrings === "string" && filterStrings == "all"){
            $("#" + this.tableID + " tbody tr").toggleClass("hidden", false);
            this.currentFilter = [];
            return true;
        } else if(col == "all"){
            return false;
        }
        $("#" + this.tableID + " tbody tr").not(".hidden").each(function(){
            var content = $(this).find(".editablegrid-" + col).text();
            if(filterStrings.includes(content)){
                $(this).toggleClass("hidden", false);
            } else {
                $(this).toggleClass("hidden", true);
            }
        });
        this.currentFilter.push({col: col, filter: filterStrings});
        return true;
    }
}