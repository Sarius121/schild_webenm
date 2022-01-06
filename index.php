<?php

include("imports.php");
require_once("config/ui-conf.php");

function getConstant(string $name, $defaultValue){
    if(defined($name)){
        return constant($name);
    } else {
        return $defaultValue;
    }
}

use ENMLibrary\LoginHandler;

$loginHandler = new LoginHandler();

$page = "login";
if(isset($_GET["page"])){
    $page = $_GET["page"];
}

if($page == "logout"){
    $loginHandler->loginWithSession();
    if(!$loginHandler->logout()){
        header("Location: .");
    }
}
else if ($page == "login" && isset($_POST['username']) && isset($_POST['password']))
{
    if($loginHandler->login($_POST['username'], $_POST['password'])){
        header("Location: .");
        exit();
    }
}
else
{
    $loginHandler->loginWithSession();
}

if($loginHandler->isLoggedIn()){
    $page = "home";
} else {
    $page = "login";
}

?>
<html>
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
        <script src="<?php echo auto_version('/lib/editablegrid/editablegrid.js'); ?>"></script>
        <!-- [DO NOT DEPLOY] --> <script src="<?php echo auto_version('/lib/editablegrid/editablegrid_renderers.js'); ?>" ></script>
		<!-- [DO NOT DEPLOY] --> <script src="<?php echo auto_version('/js/editablegrid_editors.js'); ?>" ></script>
		<!-- [DO NOT DEPLOY] --> <script src="<?php echo auto_version('/lib/editablegrid/editablegrid_validators.js'); ?>" ></script>
		<!-- [DO NOT DEPLOY] --> <script src="<?php echo auto_version('/lib/editablegrid/editablegrid_utils.js'); ?>" ></script>
		<!-- [DO NOT DEPLOY] --> <script src="<?php echo auto_version('/lib/editablegrid/editablegrid_charts.js'); ?>" ></script>
        <script src="<?php echo auto_version('/js/nav.js'); ?>"></script>
        <script src="<?php echo auto_version('/js/tab-layout.js'); ?>"></script>
        <script src="<?php echo auto_version('/js/custom-editablegrid.js'); ?>"></script>
        <script src="<?php echo auto_version('/js/gradeTable.js'); ?>"></script>
        <script src="<?php echo auto_version('/js/grades-list.js'); ?>"></script>
        <script src="<?php echo auto_version('/js/textarea-caret-position.js'); ?>"></script>
        <script src="<?php echo auto_version('/js/classTeacherTable.js'); ?>"></script>
        <script src="<?php echo auto_version('/js/examsTable.js'); ?>"></script>

        <script src="<?php echo auto_version('/js/progress-message-box.js'); ?>"></script>
    </head>
    <body>
        <?php 
        include($page . ".php")
        ?>
    </body>
</html>