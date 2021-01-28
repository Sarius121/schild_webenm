class ExamsTable extends CustomEditableGrid{

    constructor(json, classTeacherTable){
        super("ExamsTable", json);
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