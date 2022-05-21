<?php

use ENMLibrary\BackupHandler;
use ENMLibrary\LoginHandler;
use ENMLibrary\RequestResponse;

include("imports.php");

if(!isset($_POST["action"]) || !isset($_POST["csrf_token"])){
    die(RequestResponse::ErrorResponse(RequestResponse::ERROR_MISSING_ARGUMENTS)->getResponse());
}

//try opening database
$loginHandler = new LoginHandler();
$loginHandler->loginWithSession();

if(!$loginHandler->isLoggedIn()){
    http_response_code(403);
    die();
}

if(!$loginHandler->checkCSRFToken($_POST["csrf_token"])){
    die(RequestResponse::ErrorResponse(RequestResponse::ERROR_CSRF_TOKEN)->getResponse());
}

//database is now accessable
$loginHandler->getGradeFile()->close();
$loginHandler->saveFileChanges();

$path = $loginHandler->getSourceFilename($loginHandler->getUsername());
if(!file_exists($path)){
    //http_response_code(404);
    die(RequestResponse::ErrorResponse(RequestResponse::ERROR_FUNCTION_SPECIFIC, $loginHandler->getCSRFToken())->getResponse());
}

if($_POST["action"] == "create"){
    /*
     * create download token and return it
     */
    $token = $loginHandler->generateDownloadToken();
    $response = RequestResponse::SuccessfulResponse($loginHandler->getCSRFToken());
    $response->addData("download_token", $token);
    echo $response->getResponse();
    exit;
} else if($_POST["action"] == "restore"){
    /*
     * upload grade file
     */
    if(!empty($_FILES) && isset($_FILES["backupFile"])){
        $file = $_FILES["backupFile"];
        if($file["error"] == UPLOAD_ERR_OK){
            $fileUploader = new BackupHandler();
            if($fileUploader->upload($file, $loginHandler->getPassword(), $loginHandler->getBasename($loginHandler->getUsername()))){
                $loginHandler->reopenFile(false);
                echo RequestResponse::SuccessfulResponse($loginHandler->getCSRFToken())->getResponse();
                exit;
            }
        }
    }
    echo RequestResponse::ErrorResponse(RequestResponse::ERROR_FUNCTION_SPECIFIC, $loginHandler->getCSRFToken())->getResponse();
    exit;
} else if($_POST["action"] == "undo"){
    /*
     * return to last backup
     */
    $fileUploader = new BackupHandler();
    if($fileUploader->undoBackupRestore($loginHandler->getBasename($loginHandler->getUsername()))){
        $loginHandler->reopenFile(false);
        echo RequestResponse::SuccessfulResponse($loginHandler->getCSRFToken())->getResponse();
        exit;
    }
    echo RequestResponse::ErrorResponse(RequestResponse::ERROR_FUNCTION_SPECIFIC, $loginHandler->getCSRFToken())->getResponse();
    exit;
} else{
    die(RequestResponse::ErrorResponse(RequestResponse::ERROR_WRONG_ARGUMENTS, $loginHandler->getCSRFToken())->getResponse());
}
?>