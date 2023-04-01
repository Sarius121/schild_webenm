<?php

use ENMLibrary\datasource\DataSourceModuleHelper;
use ENMLibrary\LoginHandler;
use ENMLibrary\RequestResponse;

include("includes/imports.php");

if(!isset($_POST["csrf_token"])){
    die(RequestResponse::ErrorResponse(RequestResponse::ERROR_MISSING_ARGUMENTS)->getResponse());
}

//try logging in
$loginHandler = new LoginHandler();
$loginHandler->loginWithSession();

if(!$loginHandler->isLoggedIn() || !$loginHandler->isAdmin()){
    http_response_code(403);
    die();
}

if(!$loginHandler->checkCSRFToken($_POST["csrf_token"])){
    die(RequestResponse::ErrorResponse(RequestResponse::ERROR_CSRF_TOKEN)->getResponse());
}

if(!isset($_POST["action"])){
    die(RequestResponse::ErrorResponse(RequestResponse::ERROR_MISSING_ARGUMENTS, $loginHandler->getCSRFToken())->getResponse());
}

if (!ADMIN_ALLOW_ACTIONS) {
    die(RequestResponse::ErrorResponse(RequestResponse::ERROR_FUNCTION_SPECIFIC, $loginHandler->getCSRFToken())->getResponse());
}

$targets = array();
if (isset($_POST['target'])) {
    if (DataSourceModuleHelper::createModule()->findFilename($_POST['target']) !== null) {
        $targets[] = $_POST['target'];
    }
} else {
    foreach (DataSourceModuleHelper::createModule()->getFilesInfos() as $file) {
        $targets[] = $file['user'];
    }
}

if (count($targets) == 0) {
    die(RequestResponse::ErrorResponse(RequestResponse::ERROR_WRONG_ARGUMENTS, $loginHandler->getCSRFToken())->getResponse());
}

if ($_POST["action"] == "save-changes-all" || $_POST["action"] == "discard-changes-all" || $_POST["action"] == "save-changes" || $_POST["action"] == "discard-changes") {
    /**
     * save changes of 
     */
    $processedCount = 0;
    foreach ($targets as $target) {
        $file = $loginHandler->foreignTmpFileExists($target);
        if ($file !== null) {
            if ($loginHandler->closeForeignFile($target, $_POST["action"] == "save-changes")) {
                $processedCount++;
            }
        }
    }
    if ($processedCount > 0) {
        echo RequestResponse::SuccessfulResponse($loginHandler->getCSRFToken())->getResponse();
        exit;
    }
    echo RequestResponse::ErrorResponse(RequestResponse::ERROR_FUNCTION_SPECIFIC, $loginHandler->getCSRFToken())->getResponse();
    exit;
} else if($_POST["action"] == "download-all"){
    /*
     * create download token and return it (currently only downloading everything is supported)
     */
    $token = $loginHandler->generateDownloadToken();
    $response = RequestResponse::SuccessfulResponse($loginHandler->getCSRFToken());
    $response->addData("download_token", $token);
    echo $response->getResponse();
    exit;
} else if($_POST["action"] == "delete-archives"){
    /*
     * delete archive and helper files
     */
    // TODO
    echo RequestResponse::ErrorResponse(RequestResponse::ERROR_FUNCTION_SPECIFIC, $loginHandler->getCSRFToken())->getResponse();
    exit;
} else {
    die(RequestResponse::ErrorResponse(RequestResponse::ERROR_WRONG_ARGUMENTS, $loginHandler->getCSRFToken())->getResponse());
}
    
?>