<?php

namespace ENMLibrary;

require_once("GradeFile.php");
require_once("EncryptedZipArchive.php");
require_once("SourceFileHandler.php");

class LoginHandler {

    private $loggedin;
    private $username;
    private $error = false;
    private $gradeFile;
    private $encryptedGradeFile;
    private $sourceFile;
    private $differentSessionActive = false;

    public function __construct($session_id = null)
    {
        if($session_id != null){
            session_id($session_id);
        }
        session_start();
    }

    public function login($username, $password) {
        $username = strtoupper($username);
        if($this->checkLogin($username, $password, true)){
            $this->loggedin = true;
            $this->username = $username;
            $this->createSession($username, $password);
            $success = true;
        } else {
            $success = false;
            $this->error = "Der Benutzername oder das Passwort ist falsch!";
        }
        if($this->gradeFile != null){
            //only open grade file when logged in with session -> after login user is redirected
            $this->gradeFile->close();
        }
        return $success;
    }

    public function loginWithSession(){
        if(!$this->isSessionValid()){
            //not loggedin
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
        if($this->gradeFile != null){
            $this->gradeFile->close();
        }
        return false;
    }

    private function checkLogin($username, $password, $newlogin = false){
        $success = false;

        $dbFilename = $this->getDBFilename($username);
        $this->sourceFile = new SourceFileHandler($this->getSourceFilename($username), $this->getZipFilename($username));
        $this->encryptedGradeFile = new EncryptedZipArchive($this->getZipFilename($username), TMP_GRADE_FILES_DIRECTORY . $dbFilename);
        if(file_exists(TMP_GRADE_FILES_DIRECTORY . $dbFilename)){
            //only try password
            if($this->encryptedGradeFile->checkPassword()){ //TODO not neccessary?
                $this->gradeFile = new GradeFile($dbFilename);
                $this->gradeFile->openFile();
                $success = $this->gradeFile->checkUser($password);
            }
            
        } elseif(($foreignFilename = $this->foreignTmpFileExists($username)) != false){
            $this->differentSessionActive = true;
            if(!$newlogin){
                //when it's not a new login and a foreign file is opened, the user has to login again to close the foreign session
                return false;
            }
            $this->gradeFile = new GradeFile($foreignFilename);
            if($this->gradeFile->openFile() && $this->gradeFile->checkUser($password)){
                $this->gradeFile->close();

                //save changes from foreign session and create new
                $foreignEncryptedGradeFile = new EncryptedZipArchive($this->getZipFilename($username), TMP_GRADE_FILES_DIRECTORY . $foreignFilename);
                $success = $foreignEncryptedGradeFile->close();
                if($success){
                    $foreignSourceFile = new SourceFileHandler($this->getSourceFilename($username), $this->getZipFilename($username));
                    $foreignSourceFile->saveFile(); //close is not neccessary
                    if($this->encryptedGradeFile->open()){
                        $this->gradeFile = new GradeFile($dbFilename);
                        $this->gradeFile->openFile();
                        $success = $this->gradeFile->checkUser($password);
                    } else {
                        $success = false;
                    }
                }
            } else {
                $this->gradeFile->close();
                return false;
            }
            
        } else {
            if($newlogin){
                if($this->sourceFile->openFile()){
                    //try password and extract grade file
                    if($this->encryptedGradeFile->open()){
                        $this->gradeFile = new GradeFile($dbFilename);
                        $this->gradeFile->openFile();
                        $success = $this->gradeFile->checkUser($password);
                    } else {
                        $this->sourceFile->closeFile();
                    }
                }
            } else {
                //different session was opened and closed -> user should be logged out
                $this->differentSessionActive = true;
                return false;
            }
        }
        return $success;
    }

    public function saveFileChanges(){
        if(!$this->isLoggedIn()){
            return false;
        }
        return $this->encryptedGradeFile->saveChanges() && $this->sourceFile->saveFile();
    }

    public function closeFile($saveChanges = true){
        if($this->gradeFile != null) { $this->gradeFile->close(); }
        return $this->encryptedGradeFile->close($saveChanges) && $this->sourceFile->closeFile();
    }

    public function reopenFile($saveChanges){
        if(!$this->isLoggedIn()){
            return false;
        }
        //source file is not reopened!
        if($this->closeFile($saveChanges)){
            return $this->encryptedGradeFile->open();
        }
        return false;
    }

    public function logout(){
        if(!$this->isLoggedIn()){
            return false;
        }
        if($this->closeFile()){
            $this->loggedin = false;
            $this->username = null;
            $this->gradeFile = null;
            $this->encryptedGradeFile = null;
            $this->destroySession();
            return true;
        }
        return false;
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

    public function createSession($username, $password){
        $_SESSION['username'] = $username;
        $_SESSION['password'] = $password;
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
        $found = glob(TMP_GRADE_FILES_DIRECTORY . $username . FILE_SUFFIX. "*");
        if($found == false || count($found) <= 0){
            return false;
        }
        foreach ($found as $file) {
            //TODO found more than one
            $fileArray = explode("/", $file);
            $filename = end($fileArray);
            if($filename != $this->getDBFilename($username)){
                return $filename;
            }
        }
        return false;
    }

    public function getSourceFilename($username){
        return SOURCE_GRADE_FILES_DIRECTORY . $username . FILE_SUFFIX . ".enz";
    }

    public function getZipFilename($username){
        return GRADE_FILES_DIRECTORY . $username . FILE_SUFFIX . ".enz";
    }

    public function getDBFilename($username){
        $fileID = $this->getSessionFileID();
        return $username . FILE_SUFFIX . ".enm_" . $fileID;
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

    public function getPassword(){
        return $_SESSION["password"];
    }

}

?>