<?php

namespace ENMLibrary;

require_once("GradeFile.php");
require_once("EncryptedZipArchive.php");

class LoginHandler {

    private $loggedin;
    private $username;
    private $error = false;
    private $gradeFile;
    private $encryptedGradeFile;

    public function login($username, $password) {
        if($this->checkLogin($username, $password)){
            $this->loggedin = true;
            $this->username = $username;
            return true;
        } else {
            $this->error = "Der Benutzername oder das Passwort ist falsch!";
            return false;
        }
    }

    private function checkLogin($username, $password){
        //TODO is file already opened by another session?
        $success = false;
        $tmpFilename = $this->getTmpGradeFilenameByUser($username);
        $this->encryptedGradeFile = new EncryptedZipArchive($this->getGradeFilenameByUser($username), $tmpFilename);
        if(file_exists($tmpFilename)){
            //only try password
            $success = $this->encryptedGradeFile->checkPassword($password);
        } else {
            //try password and extract grade file
            $success = $this->encryptedGradeFile->open($password);
        }
        if($success){
            $this->gradeFile = new GradeFile($this->getFullPath($tmpFilename));
            $this->gradeFile->openFile();
        }
        return $success;
    }

    public function closeFile($password){
        $this->gradeFile->close();
        $success = $this->encryptedGradeFile->close($password);
        $this->logout();
        return $success;
    }

    private function logout(){
        $this->loggedin = false;
        $this->username = null;
        $this->gradeFile = null;
        $this->encryptedGradeFile = null;
    }

    public function getFullPath($path){
        return realpath($path);
    }

    public function getTmpGradeFilenameByUser($username) {
        return 'grade-files/tmp/' . $username . ".enm";
    }

    public function getGradeFilenameByUser($username) {
        return 'grade-files/' . $username . ".enz";
    }

    public function getGradeFilename() {
        return "grade-files/" . $this->username . ".enm";
    }

    public function getGradeFile()
    {
        return $this->gradeFile;
    }

    public function isLoggedIn(){
        return $this->loggedin;
    }

    public function getError(){
        return $this->error;
    }

    public function getUsername(){
        return $this->username;
    }

}

?>