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
            classTeacherTable = new ClassTeacherTable(<?php echo $jsonClassTeacherTable; ?>);
            classTeacherTable.renderGrid();
            classTeacherTable.renderPhrasesTable(<?php echo $jsonPhrasesTable; ?>);
        });
    </script>
    <div id="classTeacherTable" class="dataTable"></div>
</div>