<div id="class-teacher-head" class="container">
    <div class="row">
        <div class="col-sm-auto text">
            <h6>Schüler:</h6> 
        </div>
        <div class="col-sm-auto">
            <button class="btn btn-primary"><</button>
        </div>
        <div class="col-sm-auto text">
            <h6>Max Mustermann (EF)</h6>
        </div>
        <div class="col-sm-auto">
            <button class="btn btn-primary">></button>
        </div>
    </div>
    <div class="row">
        <div class="col-sm">
            <label for="ASV">Arbeits- und Sozialverhalten</label>
            <textarea name="ASV" rows="8" onfocus="filterPhrasesTable('ASV', this)" readonly></textarea>
        </div>
        <div class="col-sm">
            <label for="AuE">Außerunterrichtliches Engagement</label>
            <textarea name="AuE" rows="8" onfocus="filterPhrasesTable('AUE', this)" readonly></textarea>
        </div>
        <div class="col-sm">
            <label for="ZB">Zeugnis-Bemerkung</label>
            <textarea name="ZB" rows="8" onfocus="filterPhrasesTable('ZB', this)" readonly></textarea>
        </div>
    </div>
</div>
<div id="phrases">
    <?php //TODO get phrases 
    use ENMLibrary\datatypes\PhrasesData;

    require_once("lib/ENMLibrary/datatypes/PhrasesData.php");
    
    $phrasesData = new PhrasesData($loginHandler->getGradeFile());
    
    $jsonPhrasesTable = $phrasesData->getJSON();
    ?>
    <script>
        window.addEventListener("load", function(event) {
            editableGrid = new EditableGrid("PhrasesTable", {editmode: "static"});
            editableGrid.load(<?php echo $jsonPhrasesTable; ?>);
            editableGrid.renderGrid("phrasesTable", "phrasesGrid");
            
            onPhrasesTableLoaded();
        });
    </script>
    <div id="phrasesTable" class="dataTable"></div>
</div>
<!--<div class="container bootstrap-list">
    <div class="row grid-header">
        <div class="col-sm-2">Ken.</div>
        <div class="col-sm-1">Grp.</div>
        <div class="col-sm-1">Fa.</div>
        <div class="col-sm-1">Jg.</div>
        <div class="col-sm-1">Niv.</div>
        <div class="col-sm">Text</div>
    </div>
    <div class="row">
        <div class="col-sm-2">#AU1</div>
        <div class="col-sm-1">AUE</div>
        <div class="col-sm-1"></div>
        <div class="col-sm-1"></div>
        <div class="col-sm-1"></div>
        <div class="col-sm">$Vorname$ hat mit großem Engagement an einer Studienfahrt mit den Zielen Berlin, Auschwitz und Krakau teilgenommen.</div>
    </div>
</div>-->