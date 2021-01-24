<?php 
use ENMLibrary\Modal;
use ENMLibrary\datatypes\ClassTeacherData;

require_once("lib/ENMLibrary/datatypes/ClassTeacherData.php");

$classTeacherData = new ClassTeacherData($loginHandler->getGradeFile());

$jsonClassTeacherTable = $classTeacherData->getJSON();

?>
<div id="data-class-teacher">
    <script>
        window.addEventListener("load", function(event) {
            editableGrid = new EditableGrid("ClassTeacherTable", {editmode: "static"});
            editableGrid.load(<?php echo $jsonClassTeacherTable; ?>);
            editableGrid.renderGrid("classTeacherTable", "classTeacherGrid");
            
            onClassTeacherTableLoaded();
        });
    </script>
    <div id="classTeacherTable" class="dataTable"></div>
</div>