<?php
require_once("lib/ENMLibrary/datasource/DataSourceModule.php");
require_once("lib/ENMLibrary/datasource/modules/WebDavDataSource.php");
require_once("lib/ENMLibrary/datasource/DataSourceModuleHelper.php");

use ENMLibrary\datasource\DataSourceModuleHelper;
use ENMLibrary\datasource\modules\WebDavDataSource;

$moduleHelper = new DataSourceModuleHelper(WebDavDataSource::getName());

?>

<div class="absolute-container">
    <h2>Admin page</h2>
    <ul class="nav nav-tabs mb-3" id="tabAdmin" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true">Übersicht</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button" role="tab" aria-controls="settings" aria-selected="false">Einstellungen</button>
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
                    <tr>
                        <th scope="row">1</th>
                        <td>TEST1</td>
                        <td><span class="now">jetzt</span></td>
                        <td></td>
                    </tr>
                    <tr>
                        <th scope="row">2</th>
                        <td>TEST</td>
                        <td>11.11.22 10:12</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="tab-pane fade" id="settings" role="tabpanel" aria-labelledby="settings-tab">
            <form class="needs-validation">
                <div class="row mb-3">
                    <div class="col">
                        <label for="source-module" class="col-form-label">Daten-Quellen-Modul</label>
                    </div>
                    <div class="col-auto">
                        <select id="source-module" class="form-select">
                            <?php foreach (DataSourceModuleHelper::getAvailableModules() as $module) { ?>
                                <option <?php if ($module == $moduleHelper->getModule()::getName()) { echo "selected"; } ?>><?php echo $module ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="safety-check" required>
                    <label class="form-check-label" for="safety-check">Ich bin mir bewusst, dass alle jetzigen Notendateien gelöscht werden.</label>
                </div>
                <button type="submit" class="btn btn-primary">Speichern</button>
            </form>
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