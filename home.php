<?php

use ENMLibrary\GradeFile;

if(!isset($loginHandler)){
        header("Location: index.php");
        die("An Error occurred!");
    }

    $gradeTable = $loginHandler->getGradeFile()->getTable();
    $jsonTable = $loginHandler->getGradeFile()->getJSONTable();
?>

<div id="home-container">
    <form id="logout-form" method="POST" action="?page=logout">
        <div class="row">
            <div class="col-sm">
                Angemeldet als <?php echo $loginHandler->getUsername(); ?>
            </div>
            <div class="col-sm-auto">
                <input class="btn btn-primary" type="submit" value="Abmelden">
            </div>
        </div>
    </form>
    <h2>webENM</h2>
    <div class="separator"></div>
    <div class="container tab-layout" id="menu-tab">
        <ul class="header">
            <li class="active" onclick="onTabClicked(this, 'tabDatei');">Datei</li>
            <li onclick="onTabClicked(this, 'tabLeistungsdaten');">Leistungsdaten</li>
            <li onclick="onTabClicked(this, 'tabZusaetze');">Zusätze</li>
            <li onclick="onTabClicked(this, 'tabHilfe');">Hilfe</li>
        </ul>
        <ul class="body visible" id="tabDatei">
            <li><div class="group-header">Notendatei</div>
                <ul>
                    <li>Öffnen</li>
                    <li>Schließen</li>
                </ul>
            </li>
            <li><div class="group-header">Im- / Export</div>
                <ul>
                    <li>Import</li>
                    <li>Export</li>
                </ul>
            </li>
            <li><div class="group-header">Druck</div>
                <ul>
                    <li>Formulardruck</li>
                </ul>
            </li>
            <li><div class="group-header">Sicherung</div>
                <ul>
                    <li>Erstellen</li>
                    <li>Einlesen</li>
                </ul>
            </li>
        </ul>
        <ul class="body" id="tabLeistungsdaten">
            <li><div class="group-header">Bearbeiten</div>
                <ul>
                    <li>Kopieren</li>
                    <li>Klassenleitung</li>
                    <li>Zentr. Prf.</li>
                    <li>Fördern</li>
                </ul>
            </li>
            <li><div class="group-header">Sortierung</div>
                <ul>
                    <li>Fach, Name</li>
                </ul>
            </li>
            <li><div class="group-header">Filter</div>
                <ul>
                    <li>Gruppe</li>
                </ul>
            </li>
        </ul>
        <ul class="body" id="tabZusaetze">
            <li><div class="group-header">Zusätze</div>
                <ul>
                    <li>Spalten wählen</li>
                    <li>Eingabehilfe</li>
                    <li>Einstellungen</li>
                </ul>
            </li>
        </ul>
        <ul class="body" id="tabHilfe">
            <li><div class="group-header">Hilfe</div>
                <ul>
                    <li>Dokumentation</li>
                    <li>Information</li>
                </ul>
            </li>
        </ul>
    </div>
    <!--<form id="search-form" method="GET" action="?page=home">
        <label class="header">Nach einem Benutzer suchen: </label>
        <div class="row">
            <div class="col-sm">
                <input class="form-control" type="search" name="search" placeholder="Suchbegriff" <?php if(isset($_GET['search'])){ echo 'value="' . $_GET['search'] . '"'; } ?>>
            </div>
            <div class="col-sm-auto">
                <input class="btn btn-primary" type="submit" value="Suchen">
            </div>
        </div>
    </form>-->
    <div class="separator"></div>
    <script>
			window.onload = function() {
				editableGrid = new EditableGrid("GradeTable", {editmode: "static"});
				editableGrid.load(<?php echo $jsonTable; ?>);
                editableGrid.renderGrid("gradeTable", "gradeGrid");
                
                onTableLoaded();
			} 
		</script>
    <div id="gradeTable"></div>
    <?php /*
    <div id="user-list" class="container user-list">
        <div class="row grid-header">
            <?php 
            echo '<div class="col-sm-1">Nr.</div>';
            foreach (GradeFile::COLUMNS as $col) {
                echo '<div class="col-sm">';
                echo $col['label'];
                echo "</div>";
            }
            ?>
        </div>
        <?php if(count($gradeTable) > 0){ 
            for($i = 0; $i < count($gradeTable); $i++){
                echo '<div class="row">';

                echo '<div class="col-sm-1">';
                echo $i + 1;
                echo "</div>";

                foreach (GradeFile::COLUMNS as $col) {
                    echo '<div class="col-sm">';
                    echo $gradeTable[$i][$col["name"]];
                    echo "</div>";
                }
                echo '</div>';
                
            }
        } else {?>
            <div class="row">
                <div class="col-sm">Keine Benutzer oder Gruppen gefunden.</div>
            </div>
        <?php } ?>
    </div> */ ?>
</div> 
<?php /*//User-Modal ?>
<div id="user-modal" class="modal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Passwort zurücksetzen?</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Schließen">
          <span aria-hidden="true">&times;</span>
        </button>
        </div>
        <div class="modal-body">
            <p>Die Passwörter folgender Personen werden zurückgesetzt. Sind Sie sich sicher, dass sie die Passwörter zurücksetzen wollen?</p>
            <div class="user-list container">
                    <div class="row grid-header">
                        <div class="col-sm-2">ID</div>
                        <div class="col-sm">Name</div>
                        <div class="col-sm-2">Klasse</div>
                    </div>
                    <span id="user-modal-grid">
                    </span>
                </div>
            </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Abbrechen</button>
            <form action="index.php?<?php echo $GETData; ?>" method="POST">
                <input name="reset_token" type="text" value="<?php echo $token; ?>" hidden>
                <input name="reset_pwd_id" id="user-modal-id" type="text" value="-1" hidden>
                <input type="submit" class="btn btn-primary" value="Passwort zurücksetzen">
            </form>
        </div>
    </div>
  </div>
</div>
*/ ?>