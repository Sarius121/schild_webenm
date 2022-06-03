<?php

use ENMLibrary\GradeFileDataHelper;
use ENMLibrary\LoginHandler;
use ENMLibrary\RequestResponse;

include("includes/imports.php");

// POST arguments: data (json) -> array(["table", "priKeyCol", "priKey", "col", "value"])

if(!isset($_POST["csrf_token"])){
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

if(!isset($_POST["data"])){
    die(RequestResponse::ErrorResponse(RequestResponse::ERROR_MISSING_ARGUMENTS, $loginHandler->getCSRFToken())->getResponse());
}


//database is now accessable
try{
    $data = json_decode($_POST["data"]);
} catch(Exception $e) {
    die(RequestResponse::ErrorResponse(RequestResponse::ERROR_WRONG_ARGUMENTS, $loginHandler->getCSRFToken())->getResponse());
}
//print_r($data);
//die("handling data...");

$fileHelper = new GradeFileDataHelper($loginHandler->getGradeFile());

$response = [];
$i = 0;
foreach($data as $updateRequest){
    $response[$i] = $fileHelper->getDataObject($updateRequest->table)->insertData($updateRequest->priKeyCol, $updateRequest->priKey, $updateRequest->col, $updateRequest->value);
    $i++;
}

if(array_search(false, $response)){
    echo RequestResponse::ErrorResponse(RequestResponse::ERROR_FUNCTION_SPECIFIC, $loginHandler->getCSRFToken())->getResponse();
    //echo $loginHandler->getGradeFile()->getError();
} else {
    echo RequestResponse::SuccessfulResponse($loginHandler->getCSRFToken())->getResponse();
}

$loginHandler->getGradeFile()->close();

//save file after 20 actions
if(isset($_SESSION["actionCount"])){
    $_SESSION["actionCount"] += 1;
    if(($_SESSION["actionCount"] % 20) == 0){
        $loginHandler->saveFileChanges();
    }
} else {
    $_SESSION["actionCount"] = 1;
}

?>