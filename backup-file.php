<?php

use ENMLibrary\LoginHandler;

include("imports.php");

if(!isset($_GET["action"])){
    die("missing arguments");
}

session_start();
if(!isset($_SESSION["username"]) || !isset($_SESSION["password"])){
    http_response_code(403);
    die();
}

//try opening database
$loginHandler = new LoginHandler();
$loginHandler->login($_SESSION['username'], $_SESSION['password']);
 
if(!$loginHandler->isLoggedIn()){
    http_response_code(403);
    die();
}

//database is now accessable
$loginHandler->getGradeFile()->close();

//print_r($loginHandler->getGradeFilename());

/*header("Content-Disposition: attachment; filename=\"" . $loginHandler->getGradeFilename() . "\"");
header("Pragma: public");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");*/

$path = $loginHandler->getGradeFilename();

if($_GET["action"] == "create"){
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename($path));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($path));
    ob_clean();
    flush();
    readfile($path);
    exit;
} else if($_GET["action"] == "restore"){

} else{
    die("wrong arguments");
}
?>