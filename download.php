<?php

use ENMLibrary\datasource\DataSourceModuleHelper;
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

$deleteAfterwards = false;
if ($loginHandler->isAdmin()) {
    $dataSource = DataSourceModuleHelper::createModule();
    $files = $dataSource->getFilesInfos();

    $zip = new ZipArchive();

    $downloadFile = tempnam(GRADE_FILES_DIRECTORY, "download-zip");

    $success = true;
    foreach ($files as $file) {
        if (!$zip->open($downloadFile)) {
            $success = false;
            break;
        }
        $tmpFile = tempnam(GRADE_FILES_DIRECTORY, "download");
        $dataSource->setSourceFile($file["name"]);
        $dataSource->setTargetFile($tmpFile);
        if (!$dataSource->openFile()) {
            continue;
        }
        $zip->addFile($tmpFile, $file["name"] . ".enz");
        if (!$zip->close()) {
            // always close zip file so that the tmp file can be directly removed
            $success = false;
        }
        unlink($tmpFile);
        if (!$success) {
            break;
        }
    }
    if (!$success) {
        unlink($downloadFile);
        die(RequestResponse::ErrorResponse(RequestResponse::ERROR_FUNCTION_SPECIFIC));
    }
    $filename = "grade-files.zip";
    $deleteAfterwards = true;
} else {
    //database is now accessable
    $loginHandler->getGradeFile()->close();
    $loginHandler->saveFileChanges();

    $downloadFile = $loginHandler->getZipFilename($loginHandler->getUsername());
    if(!file_exists($downloadFile)){
        http_response_code(404);
        die();
    }
    $filename = $loginHandler->getSourceFilename($loginHandler->getUsername());
}

/*
* download grade file from source directory
*/
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=' . $filename);
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($downloadFile));
ob_clean();
flush();
readfile($downloadFile);

if ($deleteAfterwards) {
    unlink($downloadFile);
}
exit;

?>