<?php

use ENMLibrary\datasource\DataSourceModuleHelper;
$module = DataSourceModuleHelper::createModule();

$fileInfos = $module->getFilesInfos();
$maxLastSaved = 0;
$maxLastUnsavedChanges = 0;
foreach ($fileInfos as $key => $fileInfo) {
    $lastSaved = $fileInfo['last-edit'];
    if ($maxLastSaved < $lastSaved) {
        $maxLastSaved = $lastSaved;
    }
    $tmpFile = $loginHandler->foreignTmpFileExists($fileInfo['name']);
    if ($tmpFile !== false) {
        $lastUnsavedChanges = filemtime(TMP_GRADE_FILES_DIRECTORY . $tmpFile);
        if ($maxLastUnsavedChanges < $lastUnsavedChanges) {
            $maxLastUnsavedChanges = $lastUnsavedChanges;
        }
        $fileInfos[$key]['last-unsaved-changes'] = $lastUnsavedChanges;
    }
}

?>

<div id="home-container">
    <div id="top-box">
        <div id="header" class="row">
            <div class="col-sm-auto">
                <img src="img/webenm-logo-color.svg" alt="logo">
            </div>
            <div class="col-sm">
                <h2>webENM <span class="admin-area">Admin-Bereich</span></h2>
            </div>
            <div class="col-sm-auto">
                Angemeldet als <?php echo $loginHandler->getUsername(); ?>
            </div>
            <div class="col-sm-auto">
                <a class="btn btn-primary" href="?page=logout">Abmelden</a>
            </div>
        </div>
        <div class="separator"></div>
        <ul id="nav-data" class="nav nav-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button id="overview-tab" class="nav-link active" data-bs-toggle="tab" data-bs-target="#overview" role="tab" aria-controls="overview" aria-selected="true">Übersicht</button>
            </li>
            <li class="nav-item" role="presentation">
                <button id="information-tab" class="nav-link" data-bs-toggle="tab" data-bs-target="#information" role="tab" aria-controls="overview" aria-selected="false">Informationen</button>
            </li>
        </ul>
    </div>
    <div id="data-container-boundary">
        <div id="data-container">
            <div class="tab-content" id="tabAdminContent">
                <div class="tab-pane show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                    <div id="overview-content">
                        <div class="dataTable">
                            <table>
                                <thead>
                                    <tr>
                                        <th class="hidden" scope="col">placeholder</th>
                                        <th scope="col">#</th>
                                        <th scope="col">Benutzername</th>
                                        <th scope="col">Zuletzt gespeichert</th>
                                        <th scope="col">Ungesicherte Änderungen</th>
                                        <?php if (ALLOW_ACTIONS) { ?>
                                            <th scope="col">Aktionen</th>
                                        <?php } ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="hidden"></td>
                                        <td></td>
                                        <td>ALLE (<?php echo count($fileInfos) ?>)</td>
                                        <td><?php if ($maxLastSaved != 0) { echo date('d-m-Y H:i', $maxLastSaved); } ?></td>
                                        <td><?php if ($maxLastUnsavedChanges != 0) { echo date('d-m-Y H:i', $maxLastUnsavedChanges); } else { echo "keine"; } ?></td>
                                        <?php if (ALLOW_ACTIONS) { ?>
                                        <td>
                                            <button class="btn btn-outline-secondary" title="Dateien als ZIP herunterladen"><svg class="bi"><use xlink:href="img/ui-icons.svg#download"/></svg></button>
                                            <?php if ($maxLastUnsavedChanges != 0) { ?>
                                                <button class="btn btn-outline-secondary" title="Speichere alle ungesicherten Änderungen"><svg class="bi"><use xlink:href="img/ui-icons.svg#save"/></svg></button>
                                                <button class="btn btn-outline-secondary" title="Lösche alle ungesicherten Änderungen"><svg class="bi"><use xlink:href="img/ui-icons.svg#delete-unsaved"/></svg></button>
                                            <?php } ?>
                                            <button class="btn btn-outline-secondary" title="Lösche alle Backups und Hilfs-Dateien"><svg class="bi"><use xlink:href="img/ui-icons.svg#delete-archive"/></svg></button>
                                        </td>
                                        <?php } ?>
                                    </tr>
                                    <?php
                                    $i = 1;
                                    foreach($fileInfos as $file) {
                                        ?>
                                        <tr>
                                            <td class="hidden"></td>
                                            <td scope="row"><?php echo $i ?></td>
                                            <td><?php echo $file["name"] ?></td>
                                            <td><?php
                                            if (time() - $file["last-edit"] < 60 * 5) {
                                                echo "<span class=\"now\">jetzt</span>";
                                            } else {
                                                echo date('d-m-Y H:i', $file["last-edit"]);
                                            }
                                            ?></td>
                                            <td><?php if (isset($file['last-unsaved-changes'])) { echo date('d-m-Y H:i', $file['last-unsaved-changes']); } else { echo "keine"; } ?></td>
                                            <?php if (ALLOW_ACTIONS) { ?>
                                            <td>
                                                <button class="btn btn-outline-secondary" title="Datei herunterladen"><svg class="bi"><use xlink:href="img/ui-icons.svg#download"/></svg></button>
                                                <?php if (isset($file['last-unsaved-changes'])) { ?>
                                                <button class="btn btn-outline-secondary" title="Speichere ungesicherte Änderungen"><svg class="bi"><use xlink:href="img/ui-icons.svg#save"/></svg></button>
                                                <button class="btn btn-outline-secondary" title="Lösche ungesicherte Änderungen"><svg class="bi"><use xlink:href="img/ui-icons.svg#delete-unsaved"/></svg></button>
                                                <?php } ?>
                                                <button class="btn btn-outline-secondary" title="Lösche Backups und Hilfs-Dateien"><svg class="bi"><use xlink:href="img/ui-icons.svg#delete-archive"/></svg></button>
                                            </td>
                                            <?php } ?>
                                        </tr>
                                        <?php
                                        $i++;
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <div id="overview-explanations">
                            <b>Erklärungen:</b>
                            <ul>
                                <li>"Jetzt" heißt in den letzten 5 Minuten.</li>
                                <li>Dateien, die nicht auf ".enz" enden, werden nicht angezeigt.</li>
                                <li>Wenn die Zeit von zuletzt gespeichert und ungesicherten Änderungen fast gleich sind, ist es wahrscheinlich, dass die Änderungen gesichert wurden, aber der Benutzer sich nicht richtig abgemeldet hat.</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="information" role="tabpanel" aria-labelledby="information-tab">
                    <div class="row">
                        <div class="col">
                            Daten-Quellen-Modul:
                        </div>
                        <div class="col-auto">
                            <?php echo $module->getName(); ?>
                        </div>
                    </div>
                    <?php
                    foreach ($module->getModuleInformation() as $key => $info) { ?>
                    <div class="row">
                    <div class="col">
                            <?php echo $key; ?>
                        </div>
                        <div class="col-auto">
                            <?php echo $info; ?>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>