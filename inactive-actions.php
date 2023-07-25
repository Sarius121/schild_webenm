<?php

use ENMLibrary\BackupHandler;
use ENMLibrary\LoggingHandler;
use ENMLibrary\LoginHandler;
use ENMLibrary\RequestResponse;

include("includes/imports.php");

if(!isset($_POST["csrf_token"])){
    die(RequestResponse::ErrorResponse(RequestResponse::ERROR_MISSING_ARGUMENTS)->getResponse());
}

//try opening database
$loginHandler = new LoginHandler();
$loginHandler->loginWithSession();

if(!$loginHandler->isLoggedIn() || $loginHandler->isAdmin()){
    http_response_code(403);
    die();
}

if(!$loginHandler->checkCSRFToken($_POST["csrf_token"])){
    LoggingHandler::getLogger()->warning("access with wrong CSRF token", [LoggingHandler::LOCATION => "inactive-actions"]);
    die(RequestResponse::ErrorResponse(RequestResponse::ERROR_CSRF_TOKEN)->getResponse());
}

try {
    if(!isset($_POST["action"])){
        die(RequestResponse::ErrorResponse(RequestResponse::ERROR_MISSING_ARGUMENTS, $loginHandler->getCSRFToken())->getResponse());
    }

    //database is now accessable

    if($_POST["action"] == "save-changes"){
        $loginHandler->getGradeFile()->close();

        if($loginHandler->saveFileChanges()){
            echo RequestResponse::SuccessfulResponse($loginHandler->getCSRFToken())->getResponse();
            exit;
        }
        echo RequestResponse::ErrorResponse(RequestResponse::ERROR_FUNCTION_SPECIFIC, $loginHandler->getCSRFToken())->getResponse();
        exit;
    } else if($_POST["action"] == "create"){
        /*
        * create download token and return it
        */
        $loginHandler->getGradeFile()->close();
        $loginHandler->saveFileChanges();

        $token = $loginHandler->generateDownloadToken();
        $response = RequestResponse::SuccessfulResponse($loginHandler->getCSRFToken());
        $response->addData("download_token", $token);
        echo $response->getResponse();
        exit;
    } else if($_POST["action"] == "restore"){
        /*
        * upload grade file
        */
        $loginHandler->getGradeFile()->close();
        $loginHandler->saveFileChanges();

        if(!empty($_FILES) && isset($_FILES["backupFile"])){
            $file = $_FILES["backupFile"];
            if($file["error"] == UPLOAD_ERR_OK){
                $fileUploader = new BackupHandler();
                if ($fileUploader->upload($file, basename($loginHandler->getSourceFilename(), ".enz"),
                        $loginHandler->getZipFilename($loginHandler->getUsername()))) {
                    $loginHandler->saveToSource();
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
        $loginHandler->getGradeFile()->close();
        $loginHandler->saveFileChanges();

        $fileUploader = new BackupHandler();
        if($fileUploader->undoBackupRestore(basename($loginHandler->getSourceFilename(), ".enz"), $loginHandler->getZipFilename($loginHandler->getUsername()))){
            $loginHandler->saveToSource();
            $loginHandler->reopenFile(false);
            echo RequestResponse::SuccessfulResponse($loginHandler->getCSRFToken())->getResponse();
            exit;
        }
        echo RequestResponse::ErrorResponse(RequestResponse::ERROR_FUNCTION_SPECIFIC, $loginHandler->getCSRFToken())->getResponse();
        exit;
    } else {
        die(RequestResponse::ErrorResponse(RequestResponse::ERROR_WRONG_ARGUMENTS, $loginHandler->getCSRFToken())->getResponse());
    }
} catch (Exception $e) {
    $errorId = LoggingHandler::logTrackableException($e);
    die(RequestResponse::ErrorResponse(RequestResponse::ERROR_UNKNOWN, $loginHandler->getCSRFToken(), $e, $errorId)->getResponse());
}
?>