/**
 * this table contains the students' exam data
 */
class ExamsTable extends CustomEditableGrid{

    constructor(json, gradesJSON){
        super("ExamsTable", json, ["Klasse", "FachKrz"]);

        var gradeValidator = new CellValidator({ 
			isValid: function(value) {
                if(value == ""){return true;}
                for (let i = 0; i < gradesJSON.length; i++) {
                    if(value == gradesJSON[i].Krz){
                        if(gradesJSON[i].Art == "N"){
                            return true;
                        }
                        return false;
                    }
                }
                return false;
             }
		});

        this.addCellValidator("Vornote", gradeValidator);
        this.addCellValidator("NoteSchriftlich", gradeValidator);
        this.addCellValidator("NoteMuendlich", gradeValidator);
        this.addCellValidator("NoteAbschluss", gradeValidator);

        const that = this;

        //create auto-complete filters
        $("#filter-exams datalist").each(function(){
            var col = this.id.replace("filter-exams-", "").replace("-options", "");
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
        $("#examsTable tbody .editablegrid-Vornote").dblclick((event) => {that.onGradeCellDoubleClicked(event)});
        $("#examsTable tbody .editablegrid-NoteSchriftlich").dblclick((event) => {that.onGradeCellDoubleClicked(event)});
        $("#examsTable tbody .editablegrid-NoteMuendlich").dblclick((event) => {that.onGradeCellDoubleClicked(event)});
        $("#examsTable tbody .editablegrid-NoteAbschluss").dblclick((event) => {that.onGradeCellDoubleClicked(event)});
    }
    
    renderGrid(){
        super.renderGrid("examsTable", "examsGrid");
    }

    onGradeCellDoubleClicked(event){
        //get row
        var cell = event.currentTarget;
        var cellClass = cell.classList.item(0);
        this.activeRow = parseInt(cell.parentElement.id.split("_")[1]);

        //show grade selection modal
        gradesModal.filterGrades("N");
        gradesModal.show(this, cellClass);
        
        cell.parentElement.classList.add("active");
    }
}