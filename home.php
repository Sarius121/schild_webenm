<?php

use ENMLibrary\BackupHandler;
use ENMLibrary\GradeFile;
use ENMLibrary\Modal;

if(!isset($loginHandler)){
        header("Location: index.php");
        die("An Error occurred!");
    }
?>

<div id="home-container">
    <?php /*<form id="logout-form" method="POST" action="?page=logout">
        <div class="row">
            <div class="col-sm">
                <a>Leistungsdaten</a>
                <a>Klassenleitung</a>
                <a>Zentr. Prf.</a>
            </div>
            <div class="col-sm-auto">
                Angemeldet als <?php echo $loginHandler->getUsername(); ?>
            </div>
            <div class="col-sm-auto">
                <input class="btn btn-primary" type="submit" value="Schließen">
            </div>
        </div>
    </form> */ ?>
    <div id="header" class="row">
        <div class="col-sm-auto">
            <img src="img/schild_logo.png">
        </div>
        <div class="col-sm">
            <h2>webENM</h2>
        </div>
        <div class="col-sm-auto">
            Angemeldet als <?php echo $loginHandler->getUsername(); ?>
        </div>
        <div class="col-sm-auto">
            <a class="btn btn-primary" href="?page=logout">Abmelden</a>
        </div>
    </div>
    <div class="separator"></div>
    <div class="container tab-layout" id="menu-tab">
        <ul class="header">
            <li class="active" onclick="onTabClicked(this, 'tabDatei');">Datei</li>
            <li onclick="onTabClicked(this, 'tabLeistungsdaten');">Leistungsdaten</li>
            <li onclick="onTabClicked(this, 'tabHilfe');">Hilfe</li>
        </ul>
        <ul class="body visible" id="tabDatei">
            <li><div class="group-header">Notendatei</div>
                <ul>
                    <li onclick="onMenuItemClicked(this, 'save-changes')">Speichern</li>
                </ul>
            </li>
            <li><div class="group-header">Druck</div>
                <ul>
                    <li class="disabled">Formulardruck</li>
                    <li class="disabled">&#9013;</li>
                </ul>
            </li>
            <?php /*<li><div class="group-header">Lokale Sicherung</div>
                <ul>
                    <li onclick="onMenuItemClicked(this, 'create-backup')">Erstellen</li>
                    <input name="backupFile" type="file" id="backupFile" accept=".enz" style="display:none" onchange="onRestoreBackupFileSelected(this.files)">
                    <li onclick="onMenuItemClicked(this, 'restore-backup')">Einlesen</li>
                    <li <?php $backupHandler = new BackupHandler(); if(!$backupHandler->oldBackupExists($loginHandler->getUsername())){ ?>class="disabled" <?php } ?> onclick="onMenuItemClicked(this, 'undo-backup')" data-tooltip="zum Stand vor dem Einlesen des Backups zurückkehren"><svg class="bi"><use xlink:href="img/ui-icons.svg#arrow-counterclockwise"/></svg></li>
                </ul>
            </li>*/ ?>
        </ul>
        <ul class="body" id="tabLeistungsdaten">
            <li><div class="group-header">Bearbeiten</div>
                <ul>
                    <li class="disabled">Fördern</li>
                </ul>
            </li>
            <li><div class="group-header">Sortierung</div>
                <ul>
                    <li class="disabled">Fach, Name</li>
                            <!--<option value="name-subject">Name, Fach</option>
                            <option value="subject-name">Fach, Name</option>
                            <option value="class-name">Klasse, Name</option>
                            <option value="class-subject">Klasse, Fach</option>-->
                    <li class="disabled">&#9013;
                        <ul class="dropdown">
                            <li>Name, Fach</li>
                            <li>Fach, Name</li>
                            <li>Klasse, Name</li>
                            <li>Klasse, Fach</li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li><div class="group-header">Filter</div>
                <ul>
                    <li class="disabled">Gruppe</li>
                    <li class="disabled">&#9013;
                        <ul class="dropdown">
                            <li>Filter Lerngruppe</li>
                            <li>Filter erstellen</li>
                            <li class="new-group">Filter löschen</li>
                        </ul>
                    </li>
                </ul>
            </li>
        </ul>
        <ul class="body" id="tabHilfe">
            <li><div class="group-header">Hilfe</div>
                <ul>
                    <li class="disabled">Dokumentation</li>
                    <li onclick="onMenuItemClicked(this, 'information')">Informationen</li>
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
    <div id="nav-data" class="nav nav-tabs">
        <div class="nav-item">
            <button class="nav-link active" onclick="onNavButtonClicked(this, 'data-grades')">Leistungsdaten</button>
        </div>
        <div class="nav-item">
            <button class="nav-link" onclick="onNavButtonClicked(this, 'data-class-teacher')">Klassenleitung</button>
        </div>
        <?php /*<div class="nav-item">
            <button class="nav-link" onclick="onNavButtonClicked(this, 'data-exams')">Zentr. Prf.</button>
        </div>*/ ?>
    </div>
    
    <div id="data-container">
        <?php 
        include("data-tabs/grades.php");
        include("data-tabs/class-teacher.php");
        //include("data-tabs/exams.php"); ?>
    </div>
</div>
<?php //grades-Modal 
    $gradesModal = Modal::defaultModal("grades-modal", "Noten", null);
    echo $gradesModal->getHTMLBeforeBody();
    include("modals/GradesModal.php");
    echo $gradesModal->getHTMLAfterBody();?>
    <script>
        gradesModal = new GradesModal();
    </script>

<?php //class-teacher-Modal 
    $classTeacherModal = new Modal("class-teacher-modal", "Klassenlehrer");
    $classTeacherModal->addButton("OK", "btn-primary", true);
    echo $classTeacherModal->getHTMLBeforeBody();
    include("modals/ClassTeacherModal.php");
    echo $classTeacherModal->getHTMLAfterBody(); 


    $loginHandler->getGradeFile()->close();

?>

<?php //information-modal
    $informationModal = new Modal("information-modal", "Informationen");
    $informationModal->addButton("OK", "btn-primary", true);
    echo $informationModal->getHTMLBeforeBody();
    ?>
    <p><b>webENM-Notenmanager für SchILD-NRW</b><br>angeglichen an den ENM-Notenmanager für SchILD-NRW des Ministeriums für Schule und Bildung, 40190 Düsseldorf (<a target="_blank" href="https://www.svws.nrw.de">https://www.svws.nrw.de</a>)</p>
    <p>Copyright © Sarius121</p>
    <p>Programmversion: 1.0 (06.2021)</p>
    <p>Diese Software ist OpenSource und in Github einsehbar: <a href="https://github.com/Sarius121/schild_webenm">https://github.com/Sarius121/schild_webenm</a></p>
    <p>Das Logo ist Eigentum des Ministeriums für Schule und Bildung, 40190 Düsseldorf<br>Die Symbole stammen von Bootstrap (<a href="https://icons.getbootstrap.com">https://icons.getbootstrap.com</a>)</p>
    <?php
    echo $informationModal->getHTMLAfterBody();
?>