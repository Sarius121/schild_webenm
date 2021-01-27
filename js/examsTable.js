class ExamsTable extends CustomEditableGrid{

    constructor(json, classTeacherTable){
        super("ExamsTable", json);
    }

    onTableRendered(){
        //TODO
    }
    
    renderGrid(){
        super.renderGrid("examsTable", "examsGrid");
    }
}