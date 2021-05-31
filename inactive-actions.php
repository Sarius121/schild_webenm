<?php

use ENMLibrary\LoginHandler;

include("imports.php");

if(!isset($_GET["action"])){
    die("missing arguments");
}

//try opening database
$loginHandler = new LoginHandler();
$loginHandler->loginWithSession();

if(!$loginHandler->isLoggedIn()){
    http_response_code(403);
    die();
}

//database is now accessable

if($_GET["action"] == "save-changes"){
    $loginHandler->getGradeFile()->close();

    if($loginHandler->saveFileChanges()){
        echo "success";
        exit;
    }
    echo "error saving changes";
    exit;
} else {
    die("wrong arguments");
}
    
?>