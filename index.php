<?php

include("imports.php");

use ENMLibrary\LoginHandler;

$loginHandler = new LoginHandler();

$page = "login";
if(isset($_GET["page"])){
    $page = $_GET["page"];
}

if($page == "logout"){
    $loginHandler->loginWithSession();
    $loginHandler->logout();
}
else if ($page == "login" && isset($_POST['username']) && isset($_POST['password']))
{
    if($loginHandler->login($_POST['username'], $_POST['password'])){
        /*$_SESSION['username'] = $_POST['username'];
        $_SESSION['password'] = $_POST['password'];
        $_SESSION['create'] = time();
        $_SESSION['expire'] = time() + 60 * 60; //current time plus 1 hour*/
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

        <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">

        <link rel="stylesheet" href="css/bootstrap.css">
        <link rel="stylesheet" href="lib/editablegrid/editablegrid.css">
        <link rel="stylesheet" href="css/style.css">
        <script src="js/jquery-3.5.1.min.js"></script>
        <script src="js/jquery-ui.min.js"></script>
        <script src="js/bootstrap.js"></script>
        <script src="lib/editablegrid/editablegrid.js"></script>
        <!-- [DO NOT DEPLOY] --> <script src="lib/editablegrid/editablegrid_renderers.js" ></script>
		<!-- [DO NOT DEPLOY] --> <script src="js/editablegrid_editors.js" ></script>
		<!-- [DO NOT DEPLOY] --> <script src="lib/editablegrid/editablegrid_validators.js" ></script>
		<!-- [DO NOT DEPLOY] --> <script src="lib/editablegrid/editablegrid_utils.js" ></script>
		<!-- [DO NOT DEPLOY] --> <script src="lib/editablegrid/editablegrid_charts.js" ></script>
        <script src="js/nav.js"></script>
        <script src="js/tab-layout.js"></script>
        <script src="js/custom-editablegrid.js"></script>
        <script src="js/gradeTable.js"></script>
        <script src="js/grades-list.js"></script>
        <script src="js/classTeacherTable.js"></script>
        <script src="js/examsTable.js"></script>
    </head>
    <body>
        <?php 
        include($page . ".php")
        ?>
    </body>
</html>