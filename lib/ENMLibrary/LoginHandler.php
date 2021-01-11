<?php

namespace ENMLibrary;

require_once("GradeFile.php");

class LoginHandler {

    private $loggedin;
    private $username;
    private $error = false;
    private $gradeFile;

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
        $this->gradeFile = new GradeFile($this->getGradeFilenameByUser($username));
        return $this->gradeFile->openFile($password);
    }

    public function getGradeFilenameByUser($username) {
        return realpath('grade-files/' . $username . ".enm");
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