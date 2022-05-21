<?php 
use ENMLibrary\datatypes\ClassTeacherData;
use ENMLibrary\datatypes\PhrasesData;


$classTeacherData = new ClassTeacherData($loginHandler->getGradeFile());
$jsonClassTeacherTable = $classTeacherData->getJSON();

$phrasesData = new PhrasesData($loginHandler->getGradeFile());
$jsonPhrasesTable = $phrasesData->getJSON();

?>
<div id="data-class-teacher">
    <script>
        window.addEventListener("load", function(event) {
            <?php if($jsonClassTeacherTable != false){ ?>
                classTeacherTable = new ClassTeacherTable(requests, <?php echo $jsonClassTeacherTable; ?>);
                classTeacherTable.renderGrid();
                classTeacherTable.renderPhrasesTable(<?php echo $jsonPhrasesTable; ?>);
            <?php } else { ?>
                document.getElementById("tab-class-teacher").classList.add("disabled");
            <?php } ?>
        });
    </script>
    <div id="classTeacherTable" class="dataTable"></div>
</div>