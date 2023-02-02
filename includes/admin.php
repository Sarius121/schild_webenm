<?php

use ENMLibrary\datasource\DataSourceModule;
use ENMLibrary\datasource\DataSourceModuleHelper;
$module = DataSourceModuleHelper::createModule();
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
                Angemeldet als Admin
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
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="hidden" scope="col">placeholder</th>
                                        <th scope="col">#</th>
                                        <th scope="col">Benutzername</th>
                                        <th scope="col">Zuletzt bearbeitet</th>
                                        <th scope="col">Aktionen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 1;
                                    foreach($module->getFilesInfos() as $file) {
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
                                            <td></td>
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