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
    private $differentSessionActive = false;

    public function __construct($session_id = null)
    {
        if($session_id != null){
            session_id($session_id);
        }
        session_start();
    }

    public function login($username, $password) {
        if($this->checkLogin($username, $password, true)){
            $this->loggedin = true;
            $this->username = $username;
            $this->createSession();
            return true;
        } else {
            $this->error = "Der Benutzername oder das Passwort ist falsch!";
            return false;
        }
    }

    public function loginWithSession(){
        if(!$this->isSessionValid()){
            $this->error = "Unbekannter Fehler. Versuche, dich erneut anzumelden.";
            return false;
        } elseif ($this->isSessionExpired()){
            $this->checkLogin($_SESSION['username'], $_SESSION['password']); //open EncryptedGradeFile to have the possibility to close it
            $this->logout($_SESSION['password']); //logout and close encrypted grade file
            $this->error = "Deine Session ist abgelaufen. Melde dich erneut an.";
            return false;
        }
        if($this->checkLogin($_SESSION['username'], $_SESSION['password'])){
            $this->loggedin = true;
            $this->username = $_SESSION['username'];
            $this->extendSession();
            return true;
        }
        if($this->differentSessionActive){
            $this->error = "Du hast dich an einem anderen Gerät angemeldet und wurdest deswegen auf diesem Gerät abgemeldet. Deine Änderungen wurden gesichert!";
        } else {
            $this->error = "Unbekannter Fehler. Versuche, dich erneut anzumelden.";
        }
        return false;
    }

    private function checkLogin($username, $password, $newlogin = false){
        //TODO is file already opened by another session?
        $success = false;
        $tmpFilename = $this->getTmpGradeFilenameByUser($username);
        $this->encryptedGradeFile = new EncryptedZipArchive($this->getGradeFilenameByUser($username), $this->getInternalGradeFilenameByUser($username), $tmpFilename);
        if(file_exists($tmpFilename)){
            //only try password
            $success = $this->encryptedGradeFile->checkPassword($password);
        } elseif(($foreignFilename = $this->foreignTmpFileExists($username)) != false){
            $this->differentSessionActive = true;
            if(!$newlogin){
                //when it's not a new login and a foreign file is opened, the user has to login again to close the foreign session
                return false;
            }
            //save changes from foreign session and create new
            $foreignEncryptedGradeFile = new EncryptedZipArchive($this->getGradeFilenameByUser($username), $this->getInternalGradeFilenameByUser($username), $foreignFilename);
            $success = $foreignEncryptedGradeFile->close($password);
            if($success){
                $success = $this->encryptedGradeFile->open($password);
            }
        } else {
            if($newlogin){
                //try password and extract grade file
                $success = $this->encryptedGradeFile->open($password);
            } else {
                //different session was opened and closed -> user should be logged out
                $this->differentSessionActive = true;
                return false;
            }
        }
        if($success){
            $this->gradeFile = new GradeFile($this->getFullPath($tmpFilename));
            $this->gradeFile->openFile();
        }
        return $success;
    }

    public function saveFileChanges(){
        if(!$this->isLoggedIn()){
            return false;
        }
        return $this->encryptedGradeFile->saveChanges($_SESSION['password']);
    }

    public function closeFile($password){
        $this->gradeFile->close();
        return $this->encryptedGradeFile->close($password);
    }

    public function logout($password = null){
        if(!$this->isLoggedIn()){
            return false;
        }
        if($password == null){
            $password = $_SESSION['password'];
        }
        $this->closeFile($password);
        $this->loggedin = false;
        $this->username = null;
        $this->gradeFile = null;
        $this->encryptedGradeFile = null;
        $this->destroySession();
    }

    public function getSessionFileID(){
        if(!isset($_SESSION['file_id'])){
            $_SESSION['file_id'] = uniqid(); //might not be uniq!
        }
        return $_SESSION['file_id'];
    }

    public function isSessionExpired(){
        return time() > $_SESSION['expire'];
    }

    public function isSessionValid(){
        return isset($_SESSION['username']) && isset($_SESSION['password']) && isset($_SESSION['expire']);
    }

    public function createSession(){
        $_SESSION['username'] = $_POST['username'];
        $_SESSION['password'] = $_POST['password'];
        $_SESSION['create'] = time();
        $this->extendSession();
    }

    public function extendSession(){
        $_SESSION['expire'] = time() + 60 * 60; //current time plus 1 hour
    }

    public function destroySession(){
        session_destroy();
    }

    private function foreignTmpFileExists($username){
        $found = glob("grade-files/tmp/" . $username . "*");
        if($found == false || count($found) <= 0){
            return false;
        }
        foreach ($found as $filename) {
            $fileIDArray = explode("_", $filename);
            $fileID = end($fileIDArray);
            if($fileID != $this->getTmpGradeFilenameByUser($username)){
                return $filename;
            }
        }
        return false;
    }

    public function getFullPath($path){
        //TODO filename handling is inefficient!!
        $splitFilename = explode("/", $path);
        return $splitFilename[count($splitFilename) - 1];
    }

    public function getTmpGradeFilenameByUser($username) {
        $fileID = $this->getSessionFileID();
        return 'grade-files/tmp/' . $username . ".enm_" . $fileID;
    }

    public function getGradeFilenameByUser($username) {
        return 'grade-files/' . $username . ".enz";
    }

    public function getInternalGradeFilenameByUser($username) {
        return $username . ".enm";
    }

    public function getGradeFilename() {
        return $this->getGradeFilenameByUser($this->username);
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