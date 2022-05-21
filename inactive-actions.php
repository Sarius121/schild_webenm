<?php

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

if($_POST["action"] == "save-changes"){
    $loginHandler->getGradeFile()->close();

    if($loginHandler->saveFileChanges()){
        echo RequestResponse::SuccessfulResponse($loginHandler->getCSRFToken())->getResponse();
        exit;
    }
    echo RequestResponse::ErrorResponse(RequestResponse::ERROR_FUNCTION_SPECIFIC)->getResponse();
    exit;
} else {
    die(RequestResponse::ErrorResponse(RequestResponse::ERROR_WRONG_ARGUMENTS)->getResponse());
}
    
?>