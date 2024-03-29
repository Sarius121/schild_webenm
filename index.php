<?php

include("includes/imports.php");
require_once("config/ui-conf.php");

use ENMLibrary\LoginHandler;

$loginHandler = new LoginHandler();

$pageArg = "login";
$page = "login";
if(isset($_GET["page"])){
    $pageArg = $_GET["page"];
}

if($pageArg == "logout"){
    if ($loginHandler->loginWithSession()) {
        $loginHandler->closeFile();
        $loginHandler->logout();
    }
    header("Location: .");
    exit();
}
else if ($pageArg == "login" && isset($_POST['username']) && isset($_POST['password']))
{
    if ($loginHandler->checkChangesToSave($_POST['username'])) {
        if ($loginHandler->checkLoginAgainstForeignFile($_POST['username'], $_POST['password'])) {
            $page = "unsaved-changes";
        }
    } else if($loginHandler->login($_POST['username'], $_POST['password'])){
        header("Location: .");
        exit();
    }
}
else if ($pageArg == "login" && (isset($_POST['yes']) || isset($_POST['no'])))
{
    // save changes
    if ($loginHandler->closeForeignFile($loginHandler->getUsername(), isset($_POST['yes']))) {
        if ($loginHandler->loginWithTmpSession()) {
            header("Location: .");
        }
    }
    exit();
}
else
{
    $loginHandler->loginWithSession();
}

if($loginHandler->isLoggedIn() && $page != "unsaved-changes"){
    if ($loginHandler->isAdmin()) {
        $page = "admin";
    } else {
        $page = "home";
    }
}

?>
<!DOCTYPE html>
<html lang="de">
    <head>
        <title>webENM</title>

        <link rel="icon" class="js-site-favicon" type="image/svg+xml" href="img/webenm-logo-color.svg">

        <link rel="stylesheet" href="<?php echo auto_version('/css/bootstrap.css'); ?>">
        <link rel="stylesheet" href="<?php echo auto_version('/lib/editablegrid/editablegrid.css'); ?>">
        <link rel="stylesheet" href="<?php echo auto_version('/css/style.css'); ?>">
        <link rel="stylesheet" href="<?php echo auto_version('/css/print.css'); ?>">

        <script src="<?php echo auto_version('/js/jquery-3.5.1.min.js'); ?>"></script>
        <script src="<?php echo auto_version('/js/jquery-ui.min.js'); ?>"></script>
        <script src="<?php echo auto_version('/js/popper.min.js'); ?>"></script>
        <script src="<?php echo auto_version('/js/bootstrap.js'); ?>"></script>

        <?php // editable-grid js files ?>
        <script src="<?php echo auto_version('/lib/editablegrid/editablegrid.js'); ?>"></script>
        <script src="<?php echo auto_version('/lib/editablegrid/editablegrid_renderers.js'); ?>" ></script>
		<script src="<?php echo auto_version('/js/editablegrid_editors.js'); ?>" ></script>
		<script src="<?php echo auto_version('/lib/editablegrid/editablegrid_validators.js'); ?>" ></script>
		<script src="<?php echo auto_version('/lib/editablegrid/editablegrid_utils.js'); ?>" ></script>
		<script src="<?php echo auto_version('/lib/editablegrid/editablegrid_charts.js'); ?>" ></script>

        <script src="<?php echo auto_version('/js/requests.js'); ?>"></script>
        <script src="<?php echo auto_version('/js/ui.js'); ?>"></script>
        <script src="<?php echo auto_version('/js/custom-editablegrid.js'); ?>"></script>
        <script src="<?php echo auto_version('/js/grade-table.js'); ?>"></script>
        <script src="<?php echo auto_version('/js/grades-modal.js'); ?>"></script>
        <script src="<?php echo auto_version('/js/textarea-caret-position.js'); ?>"></script>
        <script src="<?php echo auto_version('/js/class-teacher-table.js'); ?>"></script>
        <script src="<?php echo auto_version('/js/exams-table.js'); ?>"></script>

        <script src="<?php echo auto_version('/js/progress-message-box.js'); ?>"></script>
    </head>
    <body>
        <?php 
        include("includes/" . $page . ".php")
        ?>
    </body>
</html>

<?php
    if (!is_null($loginHandler->getGradeFile())) {
        // file is not needed anymore
        $loginHandler->getGradeFile()->close();
    }
?>