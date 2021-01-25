<?php

use ENMLibrary\GradeFile;
use ENMLibrary\Modal;

if(!isset($loginHandler)){
        header("Location: index.php");
        die("An Error occurred!");
    }

    //$gradeTable = $loginHandler->getGradeFile()->getTable();
    $jsonTable = $loginHandler->getGradeFile()->getJSONTable();

    $grades = $loginHandler->getGradeFile()->getGrades();
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
    <div class="row">
        <div class="col-sm">
            <h2>webENM</h2>
        </div>
        <div class="col-sm-auto">
            Angemeldet als <?php echo $loginHandler->getUsername(); ?>
        </div>
        <div class="col-sm-auto">
            <input class="btn btn-primary" type="submit" value="Schließen">
        </div>
    </div>
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
                    <li onclick="onMenuItemClicked(this, 'save')">Speichern</li>
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
                    <li class="disabled">Formulardruck</li>
                    <li class="disabled">&#9013;</li>
                </ul>
            </li>
            <li><div class="group-header">Sicherung</div>
                <ul>
                    <li>Erstellen</li>
                    <li>Einlesen</li>
                </ul>
            </li>
            <li><div class="group-header">Schließen</div>
                <ul>
                    <li>Schließen</li>
                </ul>
            </li>
        </ul>
        <ul class="body" id="tabLeistungsdaten">
            <li><div class="group-header">Bearbeiten</div>
                <ul>
                    <li>Kopieren</li>
                    <li>Fördern</li>
                </ul>
            </li>
            <li><div class="group-header">Sortierung</div>
                <ul>
                    <li>Fach, Name</li>
                            <!--<option value="name-subject">Name, Fach</option>
                            <option value="subject-name">Fach, Name</option>
                            <option value="class-name">Klasse, Name</option>
                            <option value="class-subject">Klasse, Fach</option>-->
                    <li>&#9013;
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
                    <li>Gruppe</li>
                    <li>&#9013;
                        <ul class="dropdown">
                            <li>Filter Lerngruppe</li>
                            <li>Filter erstellen</li>
                            <li class="new-group">Filter löschen</li>
                        </ul>
                    </li>
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
    <div id="nav-data" class="nav nav-tabs">
        <div class="nav-item">
            <button class="nav-link active" onclick="onNavButtonClicked(this, 'data-grades')">Leistungsdaten</button>
        </div>
        <div class="nav-item">
            <button class="nav-link" onclick="onNavButtonClicked(this, 'data-class-teacher')">Klassenleitung</button>
        </div>
        <div class="nav-item">
            <button class="nav-link" onclick="onNavButtonClicked(this, 'data-exams')">Zentr. Prf.</button>
        </div>
    </div>
    
    <div id="data-container">
        <?php 
        include("data-tabs/grades.php");
        include("data-tabs/class-teacher.php");
        include("data-tabs/exams.php"); ?>
    </div>
</div>
<?php //grades-Modal 
    $gradesModal = Modal::defaultModal("grades-modal", "Noten", "onGradesListOKClicked()");
    echo $gradesModal->getHTMLBeforeBody();
    include("modals/GradesModal.php");
    echo $gradesModal->getHTMLAfterBody(); ?>

<?php //class-teacher-Modal 
    $classTeacherModal = new Modal("class-teacher-modal", "Klassenlehrer");
    $classTeacherModal->addButton("OK", "btn-primary", true);
    echo $classTeacherModal->getHTMLBeforeBody();
    include("modals/ClassTeacherModal.php");
    echo $classTeacherModal->getHTMLAfterBody(); ?>