<?php

use ENMLibrary\datatypes\GradesData;

$gradesData = new GradesData($loginHandler->getGradeFile());
$grades = $gradesData->getGradesArray();
?>

<div id="grades-list" class="container bootstrap-list">
    <div class="row grid-header">
        <?php 
        echo '<div class="col-sm-1">Nr.</div>';
        foreach (GradesData::GRADES_COLUMNS as $col) {
            echo '<div class="col-sm' . $col['size'] . '">';
            echo $col['label'];
            echo "</div>";
        }
        ?>
    </div>
    <?php if(count($grades) > 0){ 
        for($i = 0; $i < count($grades); $i++){
            echo '<div class="row">';

            echo '<div class="col-sm-1">';
            echo $i + 1;
            echo "</div>";

            foreach (GradesData::GRADES_COLUMNS as $col) {
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