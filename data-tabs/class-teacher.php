<?php 
use ENMLibrary\Modal;
?>
<div id="data-class-teacher">
    <script>
        window.addEventListener("load", function(event) {
            editableGrid = new EditableGrid("ClassTeacherTable", {editmode: "static"});
            editableGrid.load(<?php echo $jsonTable; ?>);
            editableGrid.renderGrid("classTeacherTable", "classTeacherGrid");
            
            onClassTeacherTableLoaded();
        });
    </script>
    <div id="classTeacherTable" class="dataTable"></div>
</div>