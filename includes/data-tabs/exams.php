<?php 
use ENMLibrary\datatypes\ExamsData;

$examsData = new ExamsData($loginHandler->getGradeFile());
$jsonExamsTable = $examsData->getJSON();

?>
<div id="data-exams">
    <script>
        window.addEventListener("load", function(event) {
            <?php if($jsonExamsTable != false){ ?>
                examsTable = new ExamsTable(requests, <?php echo $jsonExamsTable; ?>, <?php echo json_encode($grades); ?>);
                examsTable.renderGrid();
            <?php } else { ?>
                document.getElementById("tab-exams").classList.add("disabled");
            <?php } ?>
        });
    </script>
    <div id="examsTable" class="dataTable"></div>
</div>