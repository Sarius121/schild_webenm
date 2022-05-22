<?php ?>
<div id="class-teacher-head" class="container">
    <div class="row">
        <div class="col-sm-auto text">
            <h6>Schüler:</h6> 
        </div>
        <div class="col-sm-auto">
            <button id="btn-ct-previous" class="btn btn-primary"><svg class="bi"><use xlink:href="img/ui-icons.svg#caret-left-fill"/></svg></button>
        </div>
        <div class="col-sm-auto text">
            <h6 id="ct-selected-name">Max Mustermann (EF)</h6>
        </div>
        <div class="col-sm-auto">
            <button id="btn-ct-next" class="btn btn-primary"><svg class="bi"><use xlink:href="img/ui-icons.svg#caret-right-fill"/></svg></button>
        </div>
    </div>
    <div class="row">
        <div class="col-sm">
            <label class="form-label" for="ASV">Arbeits- und Sozialverhalten</label>
            <textarea class="form-control" id="textarea-asv" name="ASV" rows="8" ></textarea>
        </div>
        <div class="col-sm">
            <label class="form-label" for="AuE">Außerunterrichtliches Engagement</label>
            <textarea class="form-control" id="textarea-aue" name="AuE" rows="8"></textarea>
        </div>
        <div class="col-sm">
            <label class="form-label" for="ZB">Zeugnis-Bemerkung</label>
            <textarea class="form-control" id="textarea-zb" name="ZB" rows="8"></textarea>
        </div>
    </div>
    <div class="row separated">
        <div class="col-sm-auto">
            <input id="multipleFirstnames" type="checkbox" class="form-check-input" checked>
            <label class="no-margin" for="multipleFirstnames" class="form-check-label">Vorname nicht mehrfach</label>
        </div>
        <div class="col-sm">
            <button class="btn btn-secondary btn-sm" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" data-tooltip="Floskelgruppen">
                <svg class="bi"><use xlink:href="img/ui-icons.svg#funnel-fill"/></svg>
            </button>
            <ul id="phraseFilterList" class="dropdown-menu">
            </ul>
        </div>
    </div>
</div>
<div id="phrases">
    <div id="phrasesTable" class="dataTable"></div>
</div>