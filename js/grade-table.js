/**
 * this table contains the students' grades
 */
class GradeTable extends CustomEditableGrid{

    loadingRemoved = false;

    constructor(json, gradesJSON){
        super("GradeTable", json, ["Klasse", "FachBez", "KurzBez"]);

        this.editableGrid.setCellEditor("NotenKrz", new UpperCaseTextEditor());

        this.addCellValidator("NotenKrz", new CellValidator({ 
			isValid: function(value) {
                if(value == ""){return true;}
                value = value.toUpperCase();
                for (let i = 0; i < gradesJSON.length; i++) {
                    if(value == gradesJSON[i].Krz){
                        return true;
                    }
                }
                return false;
             }
        }));
        
        var positiveNumberValidator = new CellValidator({ 
			isValid: function(value) {
                return (parseInt(value) >= 0);
             }
		});

        this.addCellValidator("Fehlstd", positiveNumberValidator);
        this.addCellValidator("uFehlstd", positiveNumberValidator);

        const that = this;

        //create auto-complete filters
        $("#filter-grades datalist").each(function(){
            var col = this.id.replace("filter-grades-", "").replace("-options", "");
            that.possibleFilters[col].forEach(item => {
                var option = document.createElement("option");
                option.setAttribute("value", item);
                this.appendChild(option);
            });
        });
    }

    onTableRendered(){    
        //events
        const that = this;
        $("#gradeTable tbody .editablegrid-NotenKrz").dblclick((event) => {that.onGradeCellDoubleClicked(event)});

        if(!this.loadingRemoved){
            document.getElementById("data-container-loading").remove();
            this.loadingRemoved = true;
        }
    }
    
    renderGrid(){
        super.renderGrid("gradeTable", "gradeGrid");
    }

    onGradeCellDoubleClicked(event){
        //get row
        var cell = event.currentTarget;
        var cellClass = cell.classList.item(0);
        this.activeRow = parseInt(cell.parentElement.id.split("_")[1]);

        //show grade selection modal
        gradesModal.filterGrades(null);
        gradesModal.show(this, cellClass);
        
        cell.parentElement.classList.add("active");
    }
}

/**
 * custom text editor for editablegrid which converts the input to upper case letters
 * 
 * @class UpperCaseTextEditor
 */
function UpperCaseTextEditor() 
{

};

// inherits TextCellEditor functionalities
UpperCaseTextEditor.prototype = new TextCellEditor();

// redefine displayEditor to setup autocomplete
UpperCaseTextEditor.prototype.formatValue = function(value)
{
	return value.toUpperCase();
};