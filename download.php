<?php

use ENMLibrary\LoginHandler;
use ENMLibrary\RequestResponse;

include("includes/imports.php");

if(!isset($_GET["token"])){
    die(RequestResponse::ErrorResponse(RequestResponse::ERROR_MISSING_ARGUMENTS)->getResponse());
}

//try opening database
$loginHandler = new LoginHandler();
$loginHandler->loginWithSession();

if(!$loginHandler->isLoggedIn()){
    http_response_code(403);
    die();
}

if(!$loginHandler->checkDownloadToken($_GET["token"])){
    die(RequestResponse::ErrorResponse(RequestResponse::ERROR_DOWNLOAD_TOKEN)->getResponse());
}

//database is now accessable
$loginHandler->getGradeFile()->close();
$loginHandler->saveFileChanges();

$path = $loginHandler->getSourceFilename($loginHandler->getUsername());
if(!file_exists($path)){
    http_response_code(404);
    die();
}

/*
* download grade file from source directory
*/
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

?>