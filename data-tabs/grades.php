<?php

use ENMLibrary\datatypes\StudentGradesData;
use ENMLibrary\datatypes\GradesData;

//$jsonTable = $loginHandler->getGradeFile()->getJSONTable();

$studentGradesData = new StudentGradesData($loginHandler->getGradeFile());
$jsonStudentGradesTable = $studentGradesData->getJSON();

$gradesData = new GradesData($loginHandler->getGradeFile());
$grades = $gradesData->getGradesArray();

?>
<div id="data-grades" class="visible">

    <script>
			window.onload = function() {
				/*editableGrid = new EditableGrid("GradeTable", {editmode: "static"}); //, allowSimultaneousEdition: true
				editableGrid.load();
                editableGrid.renderGrid("gradeTable", "gradeGrid");*/

                gradeTable = new GradeTable(<?php echo $jsonStudentGradesTable; ?>, <?php echo json_encode($grades); ?>);
                gradeTable.renderGrid();
			} 
		</script>
    <div id="gradeTable" class="dataTable"></div>
    <?php /*
    <div id="user-list" class="container user-list">
        <div class="row grid-header">
            <?php 
            echo '<div class="col-sm-1">Nr.</div>';
            foreach (GradeFile::COLUMNS as $col) {
                echo '<div class="col-sm">';
                echo $col['label'];
                echo "</div>";
            }
            ?>
        </div>
        <?php if(count($gradeTable) > 0){ 
            for($i = 0; $i < count($gradeTable); $i++){
                echo '<div class="row">';

                echo '<div class="col-sm-1">';
                echo $i + 1;
                echo "</div>";

                foreach (GradeFile::COLUMNS as $col) {
                    echo '<div class="col-sm">';
                    echo $gradeTable[$i][$col["name"]];
                    echo "</div>";
                }
                echo '</div>';
                
            }
        } else {?>
            <div class="row">
                <div class="col-sm">Keine Benutzer oder Gruppen gefunden.</div>
            </div>
        <?php } ?>
    </div> */ ?>
</div>