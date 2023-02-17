<?php

namespace ENMLibrary;

use ENMLibrary\datasource\DataSourceModule;
use ENMLibrary\datasource\DataSourceModuleHelper;

require_once("GradeFile.php");
require_once("EncryptedZipArchive.php");
require_once("SessionHandler.php");

class LoginHandler {

    private SessionHandler $session;

    private $loggedin;
    private $username;
    private $error = false;
    private $gradeFile;
    private $encryptedGradeFile;
    private DataSourceModule $dataSource;

    public function __construct()
    {
        $this->session = new SessionHandler();
        $this->dataSource = DataSourceModuleHelper::createModule();
    }

    public function login($username, $password, $saveChanges=true) {
        $username = $this->normalizeUsername($username);
        if ($username == ADMIN_USER) {
            // login as admin instead of normal
            return $this->loginAdmin($password);
        }
        if($this->checkLogin($username, $password, true, $saveChanges)){
            $this->loggedin = true;
            $this->username = $username;
            $this->session->createSession($username, $password);
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
        if(!$this->session->isSessionValid()){
            //not loggedin
            //$this->error = "Unbekannter Fehler. Versuche, dich erneut anzumelden.";
            return false;
        } elseif ($this->session->isAdmin()) {
            if ($this->session->isSessionExpired()) {
                $this->logout();
                return false;
            }
            $this->loggedin = true;
            $this->username = $this->session->getUsername();
            $this->session->extendSession();
            return true;
        } elseif ($this->session->isTmpSession()) {
            // logging in with tmp session is not allowed
            $this->session->destroySession();
            $this->error = "Unbekannter Fehler. Versuche, dich erneut anzumelden.";
            return false;
        } elseif ($this->session->isSessionExpired()){
            $this->checkLogin($this->session->getUsername(), $this->session->getPassword()); //open EncryptedGradeFile to have the possibility to close it
            $this->logout(); //logout and close encrypted grade file
            $this->error = "Deine Session ist abgelaufen. Melde dich erneut an.";
            return false;
        }
        if($this->checkLogin($this->session->getUsername(), $this->session->getPassword())){
            $this->loggedin = true;
            $this->username = $this->session->getUsername();
            $this->session->extendSession();
            return true;
        } else {
            if($this->error == null){
                $this->error = "Unbekannter Fehler. Versuche, dich erneut anzumelden.";
            }
            $this->session->destroySession();
        }
        if($this->gradeFile != null){
            $this->gradeFile->close();
        }
        return false;
    }

    public function loginWithTmpSession(bool $saveChanges) {
        if (!$this->session->isTmpSession()) {
            // already logged in -> someone else should handle this
            return false;
        } elseif (!$this->session->isSessionValid()) {
            $this->error = "Unbekannter Fehler. Versuche, dich erneut anzumelden.";
            $this->session->destroySession();
            return false;
        } elseif ($this->session->isSessionExpired()){
            $this->error = "Deine Session ist abgelaufen. Melde dich erneut an.";
            $this->session->destroySession();
            return false;
        }
        $username = $this->session->getUsername();
        $password = $this->session->getPassword();
        $this->session->destroySession();
        $this->session->initSession();
        return $this->login($username, $password, $saveChanges);
    }

    private function checkLogin($username, $password, $newlogin = false, $saveChanges = true){
        $success = false;

        if(is_null($this->dataSource->findFilename($username))){
            //wrong username
            $this->error = "Der Benutzername oder das Passwort ist falsch!";
            return false;
        }

        $dbFilename = $this->getDBFilename($username);
        $this->dataSource->setTargetFile($this->getZipFilename($username));
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
                    $this->dataSource->closeFile();
                }
            }
            //TODO what if db exists but working zip not or source zip?
        } elseif(($foreignFilename = $this->foreignTmpFileExists($username)) != false){
            //$this->differentSessionActive = true;
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
                $foreignDataSource = DataSourceModuleHelper::createModule();
                $foreignDataSource->findFilename($username);
                $foreignDataSource->setTargetFile($foreignZipFile);
                if($foreignEncryptedGradeFile->close($saveChanges) && $foreignDataSource->closeFile($saveChanges)){
                    $success = $this->openComplete($dbFilename, $password);
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
                $success = $this->openComplete($dbFilename, $password);
            } else {
                //different session was opened and closed -> user should be logged out
                //nothing opened yet
                //$this->differentSessionActive = true;
                $this->error = "Du hast dich an einem anderen Gerät angemeldet und wurdest deswegen auf diesem Gerät abgemeldet. Deine Änderungen wurden gesichert!";
                return false;
            }
        }
        return $success;
    }

    /**
     * open source file, zip file, grade file and check user
     * 
     * sets the error to the corresponing error message if an error occurs
     * 
     * if and error occurs the opened files are closed again without saving
     * 
     * @return bool true if openings are successful and the password is ok, false otherwise
     */
    private function openComplete(string $dbFilename, string $password) {
        if($this->dataSource->openFile()){
            //try password and extract grade file
            if($this->encryptedGradeFile->open()){
                $this->gradeFile = new GradeFile($dbFilename);
                if($this->gradeFile->openFile()){
                    if($this->gradeFile->checkUser($password)){
                        return true;
                    } else {
                        //wrong password -> close everything
                        $this->gradeFile->close();
                        $this->error = "Der Benutzername oder das Passwort ist falsch!";
                    }
                } else {
                    if($this->gradeFile->getError() != null){
                        $this->error = $this->gradeFile->getError();
                    }
                }
                $this->encryptedGradeFile->close(false);
            }
            $this->dataSource->closeFile(false);
        }
        return false;
    }

    public function checkChangesToSave(string $username) {
        $username = $this->normalizeUsername($username);

        if(is_null($this->dataSource->findFilename($username))){
            return false;
        }

        return $this->foreignTmpFileExists($username) != false;
    }

    public function checkLoginAgainstForeignFile(string $username, string $password) {
        $username = $this->normalizeUsername($username);

        if(is_null($this->dataSource->findFilename($username))){
            return false;
        }

        if (($foreignFilename = $this->foreignTmpFileExists($username)) != false) {
            $foreignGradeFile = new GradeFile($foreignFilename);
            if ($foreignGradeFile->openFile()) {
                $success = $foreignGradeFile->checkUser($password);
                $foreignGradeFile->close();
                if ($success) {
                    $this->session->createTmpSession($username, $password);
                } else {
                    $this->error = "Der Benutzername oder das Passwort ist falsch!";
                }
                return $success;
            }
        }
        $this->error = "Ein unbekannter Fehler ist aufgetreten.";
        return false;
    }

    public function saveFileChanges(){
        if(!$this->isLoggedIn()){
            return false;
        }
        return $this->encryptedGradeFile->saveChanges() && $this->dataSource->saveFile();
    }

    /**
     * saves only the zip file to the data source
     */
    public function saveToSource() {
        if(!$this->isLoggedIn()){
            return false;
        }
        return $this->dataSource->saveFile();
    }

    private function normalizeUsername(string $username) {
        return mb_strtoupper($username, 'UTF-8');
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
        if($this->dataSource != null) { $success = $success && $this->dataSource->closeFile($saveChanges); }
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
            $success = $this->dataSource->openFile();
            return $success && $this->encryptedGradeFile->open();
        }
        return false;
    }

    public function logout($force = false){
        if(!$force && !$this->isLoggedIn()){
            return false;
        }
        if($this->isAdmin() || $this->closeFile()){
            $this->loggedin = false;
            $this->username = null;
            $this->gradeFile = null;
            $this->encryptedGradeFile = null;
            $this->session->destroySession();
            return true;
        }
        return false;
    }

    /**
     * login the current user as admin if the user is allowed to
     */
    public function loginAdmin($password) {
        if ($password == ADMIN_PWD) {
            $this->session->createAdminSession();
            return true;
        }
        return false;
    }

    /**
     * check if the current user is logged in as admin
     */
    public function isAdmin() {
        if (!$this->isLoggedIn()) {
            return false;
        }
        return $this->session->isAdmin();
    }

    public function foreignTmpFileExists($username){
        $foundSrcFilename = $this->dataSource->findFilename($username);
        if (is_null($foundSrcFilename)) {
            return false;
        }
        $found = glob(TMP_GRADE_FILES_DIRECTORY . $foundSrcFilename . "*");
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
        $foundSrcFilename = $this->dataSource->findFilename($username);
        if (is_null($foundSrcFilename)) {
            return false;
        }
        $found = glob(GRADE_FILES_DIRECTORY . $foundSrcFilename . "*");
        if($found == false || count($found) <= 0){
            return false;
        }
        foreach ($found as $file) {
            //TODO found more than one
            return $file;
        }
        return false;
    }

    public function getZipFilename($username){
        return GRADE_FILES_DIRECTORY . $this->dataSource->findFilename($username) . ".enz_" . $this->session->getSessionFileID();
    }

    public function getDBFilename($username){
        $fileID = $this->session->getSessionFileID();
        return $this->dataSource->findFilename($username) . ".enm_" . $fileID;
    }

    public function getSourceFilename() {
        return $this->dataSource->findFilename($this->username);
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

    public function getCSRFToken() {
        return $this->session->getCSRFToken();
    }

    public function generateDownloadToken() {
        return $this->session->generateDownloadToken();
    }

    public function checkCSRFToken(string $token) {
        return $this->session->checkCSRFToken($token);
    }

    public function checkDownloadToken(string $token) {
        return $this->session->checkDownloadToken($token);
    }

    public function getPassword() {
        return $this->session->getPassword();
    }

}

?>