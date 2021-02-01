window.addEventListener("load", function(event) {
    //$(".editablegrid-NotenKrz").click(onGradeCellClicked);
    //$("#gradeTable .editablegrid-NotenKrz").css("color", "red");
});

class GradeTable extends CustomEditableGrid{
    constructor(json, gradesJSON){
        super("GradeTable", json);

        this.addCellValidator("NotenKrz", new CellValidator({ 
			isValid: function(value) {
                if(value == ""){return true;}
                for (let i = 0; i < gradesJSON.length; i++) {
                    if(value == gradesJSON[i].Krz){
                        return true;
                    }
                }
                return false;
             }
		}));
    }

    onTableRendered(){    
        //events
        const that = this;
        $("#gradeTable tbody .editablegrid-NotenKrz").dblclick((event) => {that.onGradeCellDoubleClicked(event)});
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

/*function onGradeTableLoaded(){
    $("#gradeTable .editablegrid-NotenKrz").dblclick(onGradeCellDoubleClicked);
}

activeRow = 1;

function onGradeCellDoubleClicked(event){
    //get row
    cell = event.currentTarget;
    activeRow = parseInt(cell.parentElement.id.split("_")[1]);

    //show grade selection modal
    //TODO show grades modal
    
    cell.parentElement.classList.add("active");
}

function onGradeCellKeyPressed(event){
    cell = event.target;
    cell.style.setProperty("background-color", "red");
}

function focusCell(row, col){
    //get row (activeRow is set in gradeTable.js)
    selector = "#GradeTable_" + row + " " + col;

    //simulate click and set value of input
    $(selector).trigger('click');
}*/