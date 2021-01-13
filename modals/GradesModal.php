<?php 
use ENMLibrary\GradeFile;
?>

<div id="grades-list" class="container grades-list">
    <div class="row grid-header">
        <?php 
        echo '<div class="col-sm-1">Nr.</div>';
        foreach (GradeFile::GRADE_COLUMNS as $col) {
            echo '<div class="col-sm' . $col['size'] . '">';
            echo $col['label'];
            echo "</div>";
        }
        ?>
    </div>
    <?php if(count($grades) > 0){ 
        for($i = 0; $i < count($grades); $i++){
            echo '<div class="row" onclick="onGradesListRowClicked(this, \'' . $grades[$i]["Krz"] . '\')" ondblclick="onGradesListRowDoubleClicked(\'' . $grades[$i]["Krz"] . '\')">';

            echo '<div class="col-sm-1">';
            echo $i + 1;
            echo "</div>";

            foreach (GradeFile::GRADE_COLUMNS as $col) {
                echo '<div class="col-sm' . $col['size'] . '">';
                echo $grades[$i][$col["name"]];
                echo "</div>";
            }
            echo '</div>';
            
        }
    } else {?>
        <div class="row">
            <div class="col-sm">Keine Noten gefunden.</div>
        </div>
    <?php } ?>
</div>