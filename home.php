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
    <div id="top-box">
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
    <div class="tab-layout-2">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="file-tab" data-bs-toggle="tab" data-bs-target="#file-tab-body" type="button" role="tab" aria-controls="file-tab-body" aria-selected="true">Datei</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="grade-data-tab" data-bs-toggle="tab" data-bs-target="#grade-data-tab-body" type="button" role="tab" aria-controls="grade-data-tab-body" aria-selected="false">Leistungsdaten</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="help-tab" data-bs-toggle="tab" data-bs-target="#help-tab-body" type="button" role="tab" aria-controls="help-tab-body" aria-selected="false">Hilfe</button>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane show active" id="file-tab-body" role="tabpanel" aria-labelledby="file-tab">
                <ul class="body visible">
                    <?php if(getConstant("ENABLE_MANUAL_SAVING", true)){ ?>
                    <li><div class="group-header">Notendatei</div>
                        <ul>
                            <li class="btn btn-outline-secondary" onclick="onMenuItemClicked(this, 'save-changes')">Speichern</li>
                        </ul>
                    </li><?php } ?>
                    <li><div class="group-header">Druck</div>
                        <ul class="btn-group" role="group">
                            <li class="btn btn-outline-secondary disabled">Formulardruck</li>
                            <li type="button" class="btn btn-outline-secondary disabled" data-bs-toggle="dropdown" aria-expanded="false"><svg class="bi"><use xlink:href="img/ui-icons.svg#chevron-down"/></svg></li>
                            <ul class="dropdown-menu dropdown-menu-end">
                            </ul>
                        </ul>
                    </li>
                    <?php if(getConstant("ENABLE_LOCAL_BACKUPS", true)){ ?>
                    <li><div class="group-header">Lokale Sicherung</div>
                        <ul class="btn-group">
                            <li class="btn btn-outline-secondary" onclick="onMenuItemClicked(this, 'create-backup')">Erstellen</li>
                            <input name="backupFile" type="file" id="backupFile" accept=".enz" style="display:none" onchange="onRestoreBackupFileSelected(this.files)">
                            <li class="btn btn-outline-secondary" onclick="onMenuItemClicked(this, 'restore-backup')">Einlesen</li>
                            <li class="btn btn-outline-secondary<?php $backupHandler = new BackupHandler(); if(!$backupHandler->oldBackupExists($loginHandler->getUsername())){ ?> disabled<?php } ?>" onclick="onMenuItemClicked(this, 'undo-backup')" data-tooltip="zum Stand vor dem Einlesen des Backups zurückkehren"><svg class="bi"><use xlink:href="img/ui-icons.svg#arrow-counterclockwise"/></svg></li>
                        </ul>
                    </li><?php } ?>
                </ul>
            </div>
            <div class="tab-pane" id="grade-data-tab-body" role="tabpanel" aria-labelledby="grade-data-tab">
                <ul class="body">
                    <li><div class="group-header">Bearbeiten</div>
                        <ul>
                            <li class="btn btn-outline-secondary disabled">Fördern</li>
                        </ul>
                    </li>
                    <li><div class="group-header">Sortierung</div>
                        <ul class="btn-group">
                            <li class="btn btn-outline-secondary" onclick="onMenuItemClicked(this, 'sort-Klasse-Name')">Klasse, Name</li>
                            <li id="sort-menu-items" class="btn btn-outline-secondary" data-bs-toggle="dropdown" aria-expanded="false"><svg class="bi"><use xlink:href="img/ui-icons.svg#chevron-down"/></svg></li>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li class="dropdown-item" onclick="onMenuItemClicked(this, 'sort-Name-Fach')">Name, Fach</li>
                                <li class="dropdown-item" onclick="onMenuItemClicked(this, 'sort-Fach-Name')">Fach, Name</li>
                                <li class="dropdown-item" onclick="onMenuItemClicked(this, 'sort-Klasse-Name')">Klasse, Name</li>
                                <li class="dropdown-item" onclick="onMenuItemClicked(this, 'sort-Klasse-Fach')">Klasse, Fach</li>
                            </ul>
                        </ul>
                    </li>
                    <li><div class="group-header">Filter</div>
                        <ul class="btn-group">
                            <li class="btn btn-outline-secondary" onclick="onMenuItemClicked(this, 'create-filter')">Filter erstellen</li>
                            <li class="btn btn-outline-secondary" onclick="onMenuItemClicked(this, 'delete-filter')" data-tooltip="Filter löschen"><svg class="bi"><use xlink:href="img/ui-icons.svg#arrow-counterclockwise"/></svg></li>
                        </ul>
                    </li>
                </ul>
            </div>
            <div class="tab-pane" id="help-tab-body" role="tabpanel" aria-labelledby="help-tab">
                <ul class="body">
                    <li><div class="group-header">Hilfe</div>
                        <ul class="btn-group">
                            <li class="btn btn-outline-secondary disabled"><svg class="bi"><use xlink:href="img/ui-icons.svg#arrow-up-right"/></svg><span> Dokumentation</span></li>
                            <li class="btn btn-outline-secondary" onclick="onMenuItemClicked(this, 'information')">Informationen</li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <?php /*<div class="separator hidden"></div>
    <div class="container tab-layout hidden" id="menu-tab">
        <ul class="header">
            <li class="active" onclick="onTabClicked(this, 'tabDatei');">Datei</li>
            <li onclick="onTabClicked(this, 'tabLeistungsdaten');">Leistungsdaten</li>
            <li onclick="onTabClicked(this, 'tabHilfe');">Hilfe</li>
        </ul>
        <ul class="body visible" id="tabDatei">
            <?php if(getConstant("ENABLE_MANUAL_SAVING", true)){ ?>
            <li><div class="group-header">Notendatei</div>
                <ul>
                    <li onclick="onMenuItemClicked(this, 'save-changes')">Speichern</li>
                </ul>
            </li><?php } ?>
            <li><div class="group-header">Druck</div>
                <ul>
                    <li class="disabled">Formulardruck</li>
                    <li class="disabled"><svg class="bi"><use xlink:href="img/ui-icons.svg#chevron-down"/></svg></li>
                </ul>
            </li>
            <?php if(getConstant("ENABLE_LOCAL_BACKUPS", true)){ ?>
            <li><div class="group-header">Lokale Sicherung</div>
                <ul>
                    <li onclick="onMenuItemClicked(this, 'create-backup')">Erstellen</li>
                    <input name="backupFile" type="file" id="backupFile-old" accept=".enz" style="display:none" onchange="onRestoreBackupFileSelected(this.files)">
                    <li onclick="onMenuItemClicked(this, 'restore-backup')">Einlesen</li>
                    <li <?php $backupHandler = new BackupHandler(); if(!$backupHandler->oldBackupExists($loginHandler->getUsername())){ ?>class="disabled" <?php } ?> onclick="onMenuItemClicked(this, 'undo-backup')" data-tooltip="zum Stand vor dem Einlesen des Backups zurückkehren"><svg class="bi"><use xlink:href="img/ui-icons.svg#arrow-counterclockwise"/></svg></li>
                </ul>
            </li><?php } ?>
        </ul>
        <ul class="body" id="tabLeistungsdaten">
            <li><div class="group-header">Bearbeiten</div>
                <ul>
                    <li class="disabled">Fördern</li>
                </ul>
            </li>
            <li><div class="group-header">Sortierung</div>
                <ul>
                    <li onclick="onMenuItemClicked(this, 'sort-Klasse-Name')">Klasse, Name</li>
                            <!--<option value="name-subject">Name, Fach</option>
                            <option value="subject-name">Fach, Name</option>
                            <option value="class-name">Klasse, Name</option>
                            <option value="class-subject">Klasse, Fach</option>-->
                    <li id="sort-menu-items-old"><svg class="bi"><use xlink:href="img/ui-icons.svg#chevron-down"/></svg>
                        <ul class="dropdown">
                            <li onclick="onMenuItemClicked(this, 'sort-Name-Fach')">Name, Fach</li>
                            <li onclick="onMenuItemClicked(this, 'sort-Fach-Name')">Fach, Name</li>
                            <li onclick="onMenuItemClicked(this, 'sort-Klasse-Name')">Klasse, Name</li>
                            <li onclick="onMenuItemClicked(this, 'sort-Klasse-Fach')">Klasse, Fach</li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li><div class="group-header">Filter</div>
                <ul>
                    <li onclick="onMenuItemClicked(this, 'create-filter')">Filter erstellen</li>
                    <li onclick="onMenuItemClicked(this, 'delete-filter')" data-tooltip="Filter löschen"><svg class="bi"><use xlink:href="img/ui-icons.svg#arrow-counterclockwise"/></svg></li>
                </ul>
            </li>
        </ul>
        <ul class="body" id="tabHilfe">
            <li><div class="group-header">Hilfe</div>
                <ul>
                    <li class="disabled"><svg class="bi"><use xlink:href="img/ui-icons.svg#arrow-up-right"/></svg><span> Dokumentation</span></li>
                    <li onclick="onMenuItemClicked(this, 'information')">Informationen</li>
                </ul>
            </li>
        </ul>
    </div>
    */ ?>
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
        <?php if(getConstant("SHOW_CLASS_TEACHER_TAB", true)){ ?>
        <div class="nav-item">
            <button class="nav-link" onclick="onNavButtonClicked(this, 'data-class-teacher')">Klassenleitung</button>
        </div><?php } ?>
        <?php if(getConstant("SHOW_EXAMS_TAB", true)){ ?>
            <div class="nav-item">
            <button class="nav-link" onclick="onNavButtonClicked(this, 'data-exams')">Zentr. Prf.</button>
        </div><?php } ?>
    </div>
    </div>
    <div id="data-container-boundary">
        <div id="data-container">
            <div id="data-container-loading" class="d-flex justify-content-center" style="margin: 1em 0; visibility: visible; position: absolute; width: 100%;"><div class="spinner-border" role="status" aria-hidden="true"></div></div>
            <?php 
            include("data-tabs/grades.php");
            if(getConstant("SHOW_CLASS_TEACHER_TAB", true)){
                include("data-tabs/class-teacher.php");
            }
            if(getConstant("SHOW_EXAMS_TAB", true)){
                include("data-tabs/exams.php");
            }
            ?>
        </div>
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

<?php //filter-Modal 
    $filterModal = Modal::defaultModal("filter-modal", "Daten filtern", "filterDataTable()");
    echo $filterModal->getHTMLBeforeBody();
    include("modals/FilterModal.php");
    echo $filterModal->getHTMLAfterBody(); 

?>

<?php //information-modal
    $informationModal = new Modal("information-modal", "Informationen");
    $informationModal->addButton("OK", "btn-primary", true);
    echo $informationModal->getHTMLBeforeBody();
    ?>
    <p><b>webENM-Notenmanager für SchILD-NRW</b><br>angeglichen an den ENM-Notenmanager für SchILD-NRW des Ministeriums für Schule und Bildung, 40190 Düsseldorf (<a target="_blank" href="https://www.svws.nrw.de">https://www.svws.nrw.de</a>)</p>
    <p>Copyright © Sarius121</p>
    <p>Programmversion: 0.1.1 (14.06.2021)</p>
    <p>Diese Software ist OpenSource und in Github einsehbar: <a href="https://github.com/Sarius121/schild_webenm">https://github.com/Sarius121/schild_webenm</a></p>
    <p>Das Logo ist Eigentum des Ministeriums für Schule und Bildung, 40190 Düsseldorf<br>Die Symbole stammen von Bootstrap (<a href="https://icons.getbootstrap.com">https://icons.getbootstrap.com</a>)</p>
    <?php
    echo $informationModal->getHTMLAfterBody();
?>