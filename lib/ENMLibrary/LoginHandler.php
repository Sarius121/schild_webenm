<?php

namespace ENMLibrary;

use ENMLibrary\datasource\DataSourceModule;
use ENMLibrary\datasource\DataSourceModuleHelper;

require_once("GradeFile.php");
require_once("EncryptedZipArchive.php");
require_once("SessionHandler.php");

class LoginHandler {

    private SessionHandler $session;

    private bool $loggedin = false;
    private ?string $username = null;
    private $error = false;
    private ?GradeFile $gradeFile = null;
    private ?EncryptedZipArchive $encryptedGradeFile = null;
    private DataSourceModule $dataSource;

    public function __construct()
    {
        $this->session = new SessionHandler();
        $this->dataSource = DataSourceModuleHelper::createModule();
    }

    /**
     * tries to login using given credentials and opens the corresponding file
     * 
     * if the user is the admin user, no file is opened
     * 
     * foreign files are always closed without saving the changes
     * 
     * @param string $username name of the user
     * @param string $password password of the user
     * @return true|false true if login was successful, false otherwise
     */
    public function login(string $username, string $password): bool {
        $username = $this->normalizeUsername($username);
        if ($username == ADMIN_USER) {
            // login as admin instead of normal
            return $this->loginAdmin($password);
        }
        if ($this->checkLogin($username, $password)) {
            $this->session->createSession($username);
            LoggingHandler::initLogger($username, $this->session->getSessionFileID());
            LoggingHandler::getLogger()->info("new login");
            $success = true;
        } else {
            $success = false;
            if($this->error == null){
                $this->error = "Unbekannter Fehler. Versuche, dich erneut anzumelden.";
            }
        }
        if ($this->gradeFile != null) {
            // only open grade file when logged in with session -> after login user is redirected
            $this->gradeFile->close();
        }
        return $success;
    }

    /**
     * tries to login with the session and opens the corresponding file
     * 
     * @return true|false true if login was successful, false otherwise
     */
    public function loginWithSession(): bool {
        if (!$this->session->isSessionValid()) {
            //not loggedin
            //$this->error = "Unbekannter Fehler. Versuche, dich erneut anzumelden.";
            return false;
        } elseif ($this->session->isSessionExpired()) {
            // don't save or discard changes -> maybe they want to be restored or not after next login
            $this->logout();
            return false;
        } elseif ($this->session->isAdmin()) {
            $this->loggedin = true;
            $this->session->extendSession();
            return true;
        } elseif ($this->session->isTmpSession()) {
            // logging in with tmp session is not allowed
            $this->session->destroySession();
            $this->error = "Unbekannter Fehler. Versuche, dich erneut anzumelden.";
            return false;
        }

        if ($this->checkSessionLogin($this->session->getUsername())) {
            $this->loggedin = true;
            $this->session->extendSession();
            LoggingHandler::initLogger($this->getUsername(), $this->session->getSessionFileID());
            return true;
        } else {
            if ($this->error == null) {
                $this->error = "Unbekannter Fehler. Versuche, dich erneut anzumelden.";
            }
            $this->session->destroySession();
        }
        if ($this->gradeFile != null) {
            $this->gradeFile->close();
        }
        return false;
    }

    /**
     * try to authenticate tmp session
     * 
     * @return true|false true if login was successful, false otherwise
     */
    public function authenticateTmpSession(): bool {
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
        LoggingHandler::initLogger($this->getUsername(), $this->session->getSessionFileID(), ["tmp" => "true"]);
        return true;
    }

    /**
     * convert tmp session into normal session
     * 
     * @return bool true if the session could be successfully created, false otherwise
     */
    public function loginWithTmpSession() : bool {
        if (!$this->isLoggedIn()) {
            return false;
        }
        $this->session->destroySession();
        $this->session->initSession();
        $this->session->createSession($this->getUsername());
        LoggingHandler::initLogger($this->getUsername(), $this->session->getSessionFileID());
        LoggingHandler::getLogger()->info("new login using tmp session");
        $dbFilename = $this->initFileObjects($this->getUsername());
        $this->openComplete($dbFilename, $this->getUsername(), false);
        $this->getGradeFile()->close();
        $this->loggedin = true;
        return true;
    }

    /**
     * tries to login and opens the grade file
     * 
     * can only handle new logins
     * 
     * @param string $username name of the user
     * @param string $password password of the user
     * @param bool $saveChanges if there is a foreign file, this determines whether the changes
     *             in this file should be saved or discarded
     * @return true|false true if login was successful, false otherwise
     */
    private function checkLogin(string $username, string $password): bool{
        if(is_null($this->dataSource->findFilename($username))){
            //wrong username
            $this->error = "Der Benutzername oder das Passwort ist falsch!";
            return false;
        }

        $dbFilename = $this->initFileObjects($username);
        if (file_exists(TMP_GRADE_FILES_DIRECTORY . $dbFilename)) {
            // if this case happens with a new login, something is wrong (probably old url) -> try with new session
            $this->logout();
            return false;
        }
        if (!$this->openComplete($dbFilename, $password)) {
            $this->closeFile(false);
            $this->error = "Der Benutzername oder das Passwort ist falsch!";
            return false;
        }
        $this->loggedin = true;
        if ($this->foreignTmpFileExists($username)) {
            // in the best case, this should be handled before calling this function
            $this->closeForeignFile($username, false);
        }
        return true;
    }

    /**
     * checks if everything is correct for a session login and opens the file
     * 
     * @param string $username name of the user
     * @return true|false true if login was successful, false otherwise
     */
    private function checkSessionLogin(string $username): bool {
        if(is_null($this->dataSource->findFilename($username))){
            //wrong username
            $this->error = "Der Benutzername oder das Passwort ist falsch!";
            return false;
        }

        $dbFilename = $this->initFileObjects($username);
        if (file_exists(TMP_GRADE_FILES_DIRECTORY . $dbFilename)) {
            if ($this->encryptedGradeFile->checkPassword()) { //TODO not neccessary? -> if this fails, something is wrong
                $this->gradeFile = new GradeFile($dbFilename);
                if ($this->gradeFile->openFile()) {
                    return true;
                } else {
                    if($this->gradeFile->getError() != null){
                        $this->error = $this->gradeFile->getError();
                    }
                    //close files and save changes if connection is not possible
                    $this->encryptedGradeFile->close();
                    $this->dataSource->closeFile();
                    return false;
                }
            }
            //TODO what if db exists but working zip not or source zip?
        } else {
            // different session was opened and closed -> user should be logged out
            // or it's not a new login and a foreign file is opened -> the user has to login again to close the foreign session
            // nothing opened yet
            $this->error = "Du hast dich an einem anderen Gerät angemeldet und wurdest deswegen auf diesem Gerät abgemeldet.";
            return false;
        }
        return false;
    }

    /**
     * open source file, zip file, grade file and check user
     * 
     * sets the error to the corresponing error message if an error occurs
     * 
     * if and error occurs the opened files are closed again without saving
     * 
     * all file objects have to be correctly created before
     * 
     * @param string $dbFilename name of the file to open
     * @param string $password password to authenticate
     * @param bool $checkPwd if set to false, $password is ignored and the file is opened without authentication
     * @return bool true if openings are successful and the password is ok, false otherwise
     */
    private function openComplete(string $dbFilename, string $password, bool $checkPwd = true): bool {
        if($this->dataSource->openFile()){
            //try password and extract grade file
            if($this->encryptedGradeFile->open()){
                $this->gradeFile = new GradeFile($dbFilename);
                if($this->gradeFile->openFile()){
                    if(!$checkPwd || $this->gradeFile->checkUser($password)){
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

    private function initFileObjects(string $username) {
        $dbFilename = $this->getDBFilename($username);
        $this->dataSource->setTargetFile($this->getZipFilename($username));
        $this->encryptedGradeFile = new EncryptedZipArchive($this->getZipFilename($username), TMP_GRADE_FILES_DIRECTORY . $dbFilename);
        return $dbFilename;
    }

    public function checkChangesToSave(string $username) {
        $username = $this->normalizeUsername($username);

        if(is_null($this->dataSource->findFilename($username))){
            return false;
        }

        return $this->foreignTmpFileExists($username) != false;
    }

    /**
     * checks login against foreign and source file
     */
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
                if (!$success) {
                    $this->error = "Der Benutzername oder das Passwort ist falsch!";
                    return false;
                }
                $dbFilename = $this->initFileObjects($username);
                
                $success = $this->openComplete($dbFilename, $password);
                $this->closeFile(false);
                if (!$success) {
                    $this->error = "Der Benutzername oder das Passwort ist falsch!";
                    return false;
                }
                $this->session->createTmpSession($username);
                return true;
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
     * open tmp file of user and close it (if some file is opened at the moment)
     * 
     * @param string $username the owner of the foreign file
     * @param bool $saveChanges true if the changes should be saved before closing the file
     */
    public function closeForeignFile($username, $saveChanges) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        $foreignFilename = $this->foreignTmpFileExists($username);
        if ($foreignFilename == false) {
            return false;
        }
        //save changes from foreign session and create new
        $foreignZipFile = $this->foreignZipFileExists($username);
        $foreignEncryptedGradeFile = new EncryptedZipArchive($foreignZipFile, TMP_GRADE_FILES_DIRECTORY . $foreignFilename);
        $foreignDataSource = DataSourceModuleHelper::createModule();
        $foreignDataSource->findFilename($username);
        $foreignDataSource->setTargetFile($foreignZipFile);
        return $foreignEncryptedGradeFile->close($saveChanges) && $foreignDataSource->closeFile($saveChanges);
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
     * @param bool $saveChanges shall the changes be saved before closing the file?, if the user is not loggedin, it's set to false
     * @return true|false true if closing succeeds or there is no file to close, false otherwise
     */
    public function closeFile(bool $saveChanges = true): bool {
        if ($this->isAdmin()) {
            return true;
        }
        $saveChanges = $this->isLoggedIn() && $saveChanges; // changes can only be saved if logged in
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
    public function reopenFile(bool $saveChanges = true): bool {
        if(!$this->isLoggedIn()){
            return false;
        }
        if($this->closeFile($saveChanges)){
            $success = $this->dataSource->openFile();
            return $success && $this->encryptedGradeFile->open();
        }
        return false;
    }

    /**
     * logs out the user
     * 
     * sets some variables to initial values and destroys session
     * 
     * doesn't close the file!!! TODO
     */
    public function logout(bool $force = false): bool {
        if(!$force && !$this->isLoggedIn()){
            return false;
        }
        $this->loggedin = false;
        $this->username = null;
        $this->gradeFile = null;
        $this->encryptedGradeFile = null;
        $this->session->destroySession();
        return true;
    }

    /**
     * login the current user as admin if the user is allowed to
     * 
     * @param string $password password of the admin
     * @return true|false true if login was successful, false otherwise
     */
    public function loginAdmin(string $password): bool {
        if ($password == ADMIN_PWD) {
            LoggingHandler::initLogger(ADMIN_USER, "-", ["privileges" => "admin"]);
            LoggingHandler::getLogger()->info("new login");
            $this->session->createAdminSession();
            return true;
        }
        $this->error = "Der Benutzername oder das Passwort ist falsch!";
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
        return $this->dataSource->findFilename($this->username) . ".enz";
    }

    public function getGradeFile()
    {
        return $this->gradeFile;
    }

    /**
     * if not done yet, tries to authenticate the user and returns true if the authentication succeeds
     * 
     * once a user is authenticated, the user is not authenticated again
     * 
     * notice that normal sessions have to be authenticated in a different way
     */
    public function isLoggedIn(){
        if ($this->loggedin) {
            return true;
        }
        if (!$this->session->isSessionValid()) {
            return false;
        }
        if ($this->session->isAdmin()) {
            return $this->loggedin = true;
        }
        if ($this->authenticateTmpSession()) {
            return $this->loggedin = true;
        }
        return false;
    }

    public function getError(){
        return $this->error;
    }

    public function getUsername(): ?string{
        if ($this->username != null) {
            return $this->username;
        }
        return $this->username = $this->session->getUsername();
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

}

?>