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
    private EncryptedZipArchive $encryptedGradeFile;
    private SourceFileHandler $sourceFile;
    private $differentSessionActive = false;

    private $basename;

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
            if($this->error == null){
                $this->error = "Unbekannter Fehler. Versuche, dich erneut anzumelden.";
            }
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
            $this->logout(); //logout and close encrypted grade file
            $this->error = "Deine Session ist abgelaufen. Melde dich erneut an.";
            return false;
        }
        if($this->checkLogin($_SESSION['username'], $_SESSION['password'])){
            $this->loggedin = true;
            $this->username = $_SESSION['username'];
            $this->extendSession();
            return true;
        } else {
            if($this->error == null){
                $this->error = "Unbekannter Fehler. Versuche, dich erneut anzumelden.";
            }
            $this->destroySession();
        }
        if($this->gradeFile != null){
            $this->gradeFile->close();
        }
        return false;
    }

    private function checkLogin($username, $password, $newlogin = false){
        $success = false;

        if(!$this->getBasename($username)){
            //wrong username
            $this->error = "Der Benutzername oder das Passwort ist falsch!";
            return false;
        }

        $dbFilename = $this->getDBFilename($username);
        $this->sourceFile = new SourceFileHandler($this->getSourceFilename($username), $this->getZipFilename($username));
        $this->encryptedGradeFile = new EncryptedZipArchive($this->getZipFilename($username), TMP_GRADE_FILES_DIRECTORY . $dbFilename);
        if(file_exists(TMP_GRADE_FILES_DIRECTORY . $dbFilename)){
            //only try password
            if($this->encryptedGradeFile->checkPassword()){ //TODO not neccessary?
                $this->gradeFile = new GradeFile($dbFilename);
                if($this->gradeFile->openFile()){
                    $success = $this->gradeFile->checkUser($password);
                    //TODO shouldn't be false because he was already loggedin -> when it's false, it's an application error -> c&!s?
                } else {
                    if($this->gradeFile->getError() != null){
                        $this->error = $this->gradeFile->getError();
                    }
                    //close files and save changes if connection is not possible
                    $this->encryptedGradeFile->close();
                    $this->sourceFile->closeFile();
                }
            }
            //TODO what if db exists but working zip not or source zip?
        } elseif(($foreignFilename = $this->foreignTmpFileExists($username)) != false){
            $this->differentSessionActive = true;
            if(!$newlogin){
                //when it's not a new login and a foreign file is opened, the user has to login again to close the foreign session
                $this->error = "Du hast dich an einem anderen Gerät angemeldet und wurdest deswegen auf diesem Gerät abgemeldet. Deine Änderungen wurden gesichert!";
                return false;
            }
            $foreignGradeFile = new GradeFile($foreignFilename);
            if($foreignGradeFile->openFile() && $foreignGradeFile->checkUser($password)){
                $foreignGradeFile->close();

                //save changes from foreign session and create new
                $foreignZipFile = $this->foreignZipFileExists($username);
                $foreignEncryptedGradeFile = new EncryptedZipArchive($foreignZipFile, TMP_GRADE_FILES_DIRECTORY . $foreignFilename);
                $foreignSourceFile = new SourceFileHandler($this->getSourceFilename($username), $foreignZipFile);
                if($foreignEncryptedGradeFile->close() && $foreignSourceFile->closeFile()){
                    if($this->sourceFile->openFile()){
                        if($this->encryptedGradeFile->open()){
                            $this->gradeFile = new GradeFile($dbFilename);
                            if($this->gradeFile->openFile()){
                                $success = $this->gradeFile->checkUser($password);
                                //TODO shouldn't be false because checkUser on foreign file worked -> when it's false, it's an application error -> c&!s?
                            } else {
                                if($this->gradeFile->getError() != null){
                                    $this->error = $this->gradeFile->getError();
                                }
                                //close files and don't save changes (there are no changes) if connection is not possible
                                $this->encryptedGradeFile->close(false);
                                $this->sourceFile->closeFile(false);
                            }
                        } else {
                            $this->sourceFile->closeFile(false);
                        }
                    } else {
                        //nothing opened yet
                    }
                } else {
                    //nothing opened yet
                }
            } else {
                if($foreignGradeFile->getError() != null){
                    $this->error = $foreignGradeFile->getError();
                } else {
                    $this->error = "Der Benutzername oder das Passwort ist falsch!";
                }
                //connection not possible or wrong username or password -> not your file, don't close it
                $foreignGradeFile->close();
                //doesn't matter -> nothing opened yet
                return false;
            }
            
        } else {
            if($newlogin){
                if($this->sourceFile->openFile()){
                    //try password and extract grade file
                    if($this->encryptedGradeFile->open()){
                        $this->gradeFile = new GradeFile($dbFilename);
                        if($this->gradeFile->openFile()){
                            if($this->gradeFile->checkUser($password)){
                                $success = true;
                            } else {
                                //wrong password -> close everything
                                $this->gradeFile->close();
                                $this->encryptedGradeFile->close(false);
                                $this->sourceFile->closeFile(false);
                                $this->error = "Der Benutzername oder das Passwort ist falsch!";
                            }
                        } else {
                            if($this->gradeFile->getError() != null){
                                $this->error = $this->gradeFile->getError();
                            }
                            //close files and don't save changes (there are no changes) if connection is not possible
                            $this->encryptedGradeFile->close(false);
                            $this->sourceFile->closeFile(false);
                        }
                    } else {
                        $this->sourceFile->closeFile();
                    }
                }
            } else {
                //different session was opened and closed -> user should be logged out
                //nothing opened yet
                $this->differentSessionActive = true;
                $this->error = "Du hast dich an einem anderen Gerät angemeldet und wurdest deswegen auf diesem Gerät abgemeldet. Deine Änderungen wurden gesichert!";
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

    /**
     * close file completely (tmp-file and working-file)
     * 
     * @param bool $saveChanges shall the changes be saved before closing the file?
     * @return true|false true if closing succeeds, false otherwise
     */
    public function closeFile($saveChanges = true){
        if($this->gradeFile != null) { $this->gradeFile->close(); }
        $success = true;
        if($this->encryptedGradeFile != null) { $success = $this->encryptedGradeFile->close($saveChanges); }
        if($this->sourceFile != null) { $success = $success && $this->sourceFile->closeFile($saveChanges); }
        return $success;
    }

    /**
     * reopen file
     * 
     * @param bool $saveChanges shall the changes be saved before reopening the file?
     * @return true|false true if reopening succeeds, false otherwise
     */
    public function reopenFile($saveChanges = true){
        if(!$this->isLoggedIn()){
            return false;
        }
        if($this->closeFile($saveChanges)){
            $success = $this->sourceFile->openFile();
            return $success && $this->encryptedGradeFile->open();
        }
        return false;
    }

    public function logout($force = false){
        if(!$force && !$this->isLoggedIn()){
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
        $found = glob(TMP_GRADE_FILES_DIRECTORY . $this->getBasename($username) . "*");
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

    private function foreignZipFileExists($username){
        $found = glob(GRADE_FILES_DIRECTORY . $this->getBasename($username) . "*");
        if($found == false || count($found) <= 0){
            return false;
        }
        foreach ($found as $file) {
            //TODO found more than one
            return $file;
        }
        return false;
    }

    public function getSourceFilename($username){
        return SOURCE_GRADE_FILES_DIRECTORY . $this->getBasename($username) . ".enz";
    }

    public function getZipFilename($username){
        return GRADE_FILES_DIRECTORY . $this->getBasename($username) . ".enz_" . $this->getSessionFileID();
    }

    public function getDBFilename($username){
        $fileID = $this->getSessionFileID();
        return $this->getBasename($username) . ".enm_" . $fileID;
    }

    public function getBasename($username){
        if($this->basename != null){
            return $this->basename;
        }
        $found = glob(SOURCE_GRADE_FILES_DIRECTORY . $username . FILE_SUFFIX . "*.enz");
        if($found == false || count($found) <= 0){
            return false;
        }
        foreach ($found as $file) {
            //take first
            $this->basename = basename($file, ".enz");
            return $this->basename;
        }
        return false;
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