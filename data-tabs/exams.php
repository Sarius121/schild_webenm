<?php 
use ENMLibrary\datatypes\ExamsData;

$examsData = new ExamsData($loginHandler->getGradeFile());
$jsonExamsTable = $examsData->getJSON();

?>
<div id="data-exams">
    <script>
        window.addEventListener("load", function(event) {
            examsTable = new ExamsTable(<?php echo $jsonExamsTable; ?>);
            examsTable.renderGrid();
        });
    </script>
    <div id="examsTable" class="dataTable"></div>
</div>