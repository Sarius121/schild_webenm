<?php

use ENMLibrary\datatypes\GradesData;
use ENMLibrary\datatypes\PhrasesData;
use ENMLibrary\GradeFileDataHelper;
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
    LoggingHandler::getLogger()->warning("access with wrong CSRF token", [LoggingHandler::LOCATION => "fetch-data"]);
    die(RequestResponse::ErrorResponse(RequestResponse::ERROR_CSRF_TOKEN)->getResponse());
}

try {
    if(!isset($_POST["tables"])){
        die(RequestResponse::ErrorResponse(RequestResponse::ERROR_MISSING_ARGUMENTS, $loginHandler->getCSRFToken())->getResponse());
    }

    //database is now accessible
    try{
        $data = json_decode($_POST["tables"]);
    } catch(Exception $e) {
        die(RequestResponse::ErrorResponse(RequestResponse::ERROR_WRONG_ARGUMENTS, $loginHandler->getCSRFToken())->getResponse());
    }

    $fileHelper = new GradeFileDataHelper($loginHandler->getGradeFile());

    $result = [];

    foreach($data as $table) {
        if($table == "Grades"){
            $gradesData = new GradesData($loginHandler->getGradeFile());
            $result[$table] = $gradesData->getGradesArray();
        } else if($table == "Phrases"){
            if(getConstant("SHOW_CLASS_TEACHER_TAB", false)){
                $phrasesData = new PhrasesData($loginHandler->getGradeFile());
                $result[$table] = $phrasesData->getJSON();
            }
        } else {
            if($table == "ClassTeacherTable" && !getConstant("SHOW_CLASS_TEACHER_TAB", false)){
                continue;
            } else if($table == "ExamsTable" && !getConstant("SHOW_EXAMS_TAB", false)){
                continue;
            }
            $result[$table] = $fileHelper->getDataObject($table)->getJSON();
        }
    }

    $response = RequestResponse::SuccessfulResponse($loginHandler->getCSRFToken());
    $response->addData("data", $result);
    echo $response->getResponse();

    $loginHandler->getGradeFile()->close();
} catch (Exception $e) {
    $errorId = LoggingHandler::logTrackableException($e);
    die(RequestResponse::ErrorResponse(RequestResponse::ERROR_UNKNOWN, $loginHandler->getCSRFToken(), $e, $errorId)->getResponse());
}

?>