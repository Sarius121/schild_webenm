<?php

use ENMLibrary\datatypes\GradesData;
use ENMLibrary\datatypes\PhrasesData;
use ENMLibrary\GradeFileDataHelper;
use ENMLibrary\LoginHandler;
use ENMLibrary\RequestResponse;

include("includes/imports.php");

if(!isset($_POST["tables"]) || !isset($_POST["csrf_token"])){
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

?>