<?php

use ENMLibrary\GradeFileDataHelper;
use ENMLibrary\LoginHandler;

include("imports.php");

// POST arguments: session_id, data (json) -> array(["table", "priKeyCol", "priKey", "col", "value"])
if(!isset($_POST["session_id"])){
    http_response_code(403);
    die();
}

if(!isset($_POST["data"])){
    die("missing arguments");
}

session_id($_POST["session_id"]);
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
try{
    $data = json_decode($_POST["data"]);
} catch(Exception $e) {
    die("cannot decode arguments");
}
print_r($data);
//die("handling data...");

$fileHelper = new GradeFileDataHelper($loginHandler->getGradeFile());

$response = [];
$i = 0;
foreach($data as $updateRequest){
    $response[$i] = $fileHelper->getDataObject($updateRequest->table)->insertData($updateRequest->priKeyCol, $updateRequest->priKey, $updateRequest->col, $updateRequest->value);
    $i++;
}

if(array_search(false, $response)){
    echo $loginHandler->getGradeFile()->getError();
}

$loginHandler->getGradeFile()->close();

?>