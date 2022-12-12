<?php

use ENMLibrary\datasource\DataSourceModuleHelper;

?>

<div class="absolute-container">
    <h2>Admin-Seite</h2>
    <ul class="nav nav-tabs mb-3" id="tabAdmin" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true">Übersicht</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="information-tab" data-bs-toggle="tab" data-bs-target="#information" type="button" role="tab" aria-controls="information" aria-selected="false">Informationen</button>
        </li>
    </ul>
    <div class="tab-content" id="tabAdminContent">
        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
            <table class="table">
                <thead>
                    <tr>
                    <th scope="col">#</th>
                    <th scope="col">Benutzername</th>
                    <th scope="col">Zuletzt bearbeitet</th>
                    <th scope="col">Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    foreach(DataSourceModuleHelper::createModule()->getFilesInfos() as $file) {
                        ?>
                        <tr>
                            <th scope="row"><?php echo $i ?></th>
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
            "Jetzt" heißt in den letzten 5 Minuten.
        </div>
        <div class="tab-pane fade" id="information" role="tabpanel" aria-labelledby="information-tab">
            <div class="row">
                <div class="col">
                    Daten-Quellen-Modul:
                </div>
                <div class="col-auto">
                    <?php echo DataSourceModuleHelper::getModuleName(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="footer">
    <div>
        <a href="doc/webENM Benutzerdokumentation.pdf" target="_blank">Dokumentation</a>
    </div>
    <div>
        webENM
    </div><div>
        &copy; Sarius121
    </div>
</div>