<?php

use ENMLibrary\BackupHandler;
use ENMLibrary\Modal;

if(!isset($loginHandler)){
        header("Location: index.php");
        die("An Error occurred!");
    }
?>

<div id="csrf_token"><?php echo $loginHandler->getCSRFToken(); ?></div>

<div id="home-container" class="big-container">
    <div id="top-box">
        <div id="header" class="row">
            <div class="col-sm-auto">
                <img src="img/webenm-logo-color.svg" alt="logo">
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
                                <li class="btn btn-outline-secondary nav-menu-button" name="save-changes">Speichern</li>
                            </ul>
                        </li><?php } ?>
                        <li><div class="group-header">Druck</div>
                            <ul>
                                <li class="btn btn-outline-secondary nav-menu-button" name="fast-print">Schnelldruck</li>
                            </ul>
                        </li>
                        <?php if(getConstant("ENABLE_LOCAL_BACKUPS", true)){ ?>
                        <li><div class="group-header">Lokale Sicherung</div>
                            <ul class="btn-group">
                                <li class="btn btn-outline-secondary nav-menu-button" name="create-backup">Erstellen</li>
                                <input name="backupFile" type="file" id="backupFile" accept=".enz">
                                <li class="btn btn-outline-secondary nav-menu-button" name="restore-backup">Einlesen</li>
                                <li class="btn btn-outline-secondary nav-menu-button<?php $backupHandler = new BackupHandler(); if(!$backupHandler->oldBackupExists($loginHandler->getUsername())){ ?> disabled<?php } ?>" name="undo-backup" data-tooltip="zum Stand vor dem Einlesen des Backups zurückkehren"><svg class="bi"><use xlink:href="img/ui-icons.svg#arrow-counterclockwise"/></svg></li>
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
                                <li class="btn btn-outline-secondary nav-menu-button" name="sort-Klasse-Name">Klasse, Name</li>
                                <li id="sort-menu-items" class="btn btn-outline-secondary" data-bs-toggle="dropdown" aria-expanded="false"><svg class="bi"><use xlink:href="img/ui-icons.svg#chevron-down"/></svg></li>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li class="dropdown-item nav-menu-button" name="sort-Name-Fach">Name, Fach</li>
                                    <li class="dropdown-item nav-menu-button" name="sort-Fach-Name">Fach, Name</li>
                                    <li class="dropdown-item nav-menu-button" name="sort-Klasse-Fach">Klasse, Fach</li>
                                </ul>
                            </ul>
                        </li>
                        <li><div class="group-header">Filter</div>
                            <ul class="btn-group">
                                <li class="btn btn-outline-secondary nav-menu-button" name="create-filter">Filter erstellen</li>
                                <li class="btn btn-outline-secondary nav-menu-button" name="delete-filter" data-tooltip="Filter löschen"><svg class="bi"><use xlink:href="img/ui-icons.svg#arrow-counterclockwise"/></svg></li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="tab-pane" id="help-tab-body" role="tabpanel" aria-labelledby="help-tab">
                    <ul class="body">
                        <li><div class="group-header">Hilfe</div>
                            <ul class="btn-group">
                                <li class="btn btn-outline-secondary nav-menu-button" name="documentation"><svg class="bi"><use xlink:href="img/ui-icons.svg#arrow-up-right"/></svg><span> Dokumentation</span></li>
                                <li class="btn btn-outline-secondary nav-menu-button" name="information">Informationen</li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="separator"></div>
        <ul id="nav-data" class="nav nav-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button id="tab-grades" class="nav-link active nav-data-tabs-button" name="data-grades" data-bs-toggle="tab" data-bs-target="#data-grades" role="tab" aria-controls="data-grades" aria-selected="true">Leistungsdaten</button>
            </li>
            <?php if(getConstant("SHOW_CLASS_TEACHER_TAB", true)){ ?>
            <li class="nav-item" role="presentation">
                <button id="tab-class-teacher" class="nav-link nav-data-tabs-button" name="data-class-teacher" data-bs-toggle="tab" data-bs-target="#data-class-teacher" role="tab" aria-controls="data-class-teacher" aria-selected="false">Klassenleitung</button>
            </li><?php } ?>
            <?php if(getConstant("SHOW_EXAMS_TAB", true)){ ?>
            <li class="nav-item" role="presentation">
                <button id="tab-exams" class="nav-link nav-data-tabs-button" name="data-exams" data-bs-toggle="tab" data-bs-target="#data-exams" role="tab" aria-controls="data-exans" aria-selected="false">Zentr. Prf.</button>
            </li><?php } ?>
        </ul>
    </div>
    <div id="print-header">
        <p>Lehrer: <?php echo $loginHandler->getUsername(); ?> | Datum: <?php echo date("d.m.Y"); ?></p>
    </div>
    <div id="data-container-boundary">
        <div id="data-container" class="tab-content">
            <div id="data-container-loading" class="d-flex justify-content-center"><div class="spinner-border" role="status" aria-hidden="true"></div></div>
            <div id="data-grades" class="tab-pane active" role="tabpanel" aria-labelledby="tab-grades">
                <div id="gradeTable" class="dataTable"></div>
            </div>
            <?php if(getConstant("SHOW_CLASS_TEACHER_TAB", true)){
                ?>
                <div id="data-class-teacher" class="tab-pane" role="tabpanel" aria-labelledby="tab-class-teacher">
                    <div id="classTeacherTable" class="dataTable"></div>
                </div>
                <?php 
            }
            if(getConstant("SHOW_EXAMS_TAB", true)){
                ?>
                <div id="data-exams" class="tab-pane" role="tabpanel" aria-labelledby="tab-exams">
                    <div id="examsTable" class="dataTable"></div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</div>
<?php //grades-Modal 
    $gradesModal = Modal::defaultModal("grades-modal", "Noten", null);
    echo $gradesModal->getHTMLBeforeBody();
    include("includes/modals/GradesModal.php");
    echo $gradesModal->getHTMLAfterBody();?>

<?php //class-teacher-Modal 
    $classTeacherModal = new Modal("class-teacher-modal", "Klassenlehrer");
    $classTeacherModal->addButton("OK", "btn-primary", true);
    echo $classTeacherModal->getHTMLBeforeBody();
    include("includes/modals/ClassTeacherModal.php");
    echo $classTeacherModal->getHTMLAfterBody(); 


    $loginHandler->getGradeFile()->close();

?>

<?php //filter-Modal 
    $filterModal = Modal::defaultModal("filter-modal", "Daten filtern", "filter-data-table-button");
    echo $filterModal->getHTMLBeforeBody();
    include("includes/modals/FilterModal.php");
    echo $filterModal->getHTMLAfterBody(); 

?>

<?php //information-modal
    $informationModal = new Modal("information-modal", "Informationen");
    $informationModal->addButton("OK", "btn-primary", true);
    echo $informationModal->getHTMLBeforeBody();
    ?>
    <p><b>webENM-Notenmanager für SchILD-NRW</b><br>angeglichen an den ENM-Notenmanager für SchILD-NRW des Ministeriums für Schule und Bildung, 40190 Düsseldorf (<a target="_blank" href="https://www.svws.nrw.de">https://www.svws.nrw.de</a>)</p>
    <p>Copyright © Sarius121</p>
    <p>Programmversion: 0.2.0 (12.12.2022)</p>
    <p>Diese Software ist OpenSource und in Github einsehbar: <a href="https://github.com/Sarius121/schild_webenm" target="_blank">https://github.com/Sarius121/schild_webenm</a></p>
    <?php
    echo $informationModal->getHTMLAfterBody();
?>