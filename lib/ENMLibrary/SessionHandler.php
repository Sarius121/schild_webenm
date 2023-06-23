<?php

namespace ENMLibrary;

class SessionHandler {

    public function __construct()
    {
        session_start();
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
        return isset($_SESSION['create']) && isset($_SESSION['expire']) 
            && isset($_SESSION['username']) && isset($_SESSION['admin'])
            && isset($_SESSION['tmp']);
    }

    public function createSession($username){
        $_SESSION['username'] = $username;
        $_SESSION['create'] = time();
        $_SESSION['tmp'] = false;
        $_SESSION['admin'] = false;
        $this->generateCSRFToken();
        $this->extendSession();
    }

    public function createTmpSession(string $username) {
        $_SESSION['username'] = $username;
        $_SESSION['tmp'] = true;
        $_SESSION['create'] = time();
        $_SESSION['admin'] = false;
        // 5 minutes
        $_SESSION['expire'] = time() + 60 * 5;
    }

    public function createAdminSession() {
        $_SESSION['username'] = ADMIN_USER;
        $_SESSION['tmp'] = false;
        $_SESSION['create'] = time();
        $_SESSION['admin'] = true;
        $this->generateCSRFToken();
        // 5 minutes
        $_SESSION['expire'] = time() + 60 * 5;
    }

    public function extendSession(){
        $_SESSION['expire'] = time() + 60 * 60; //current time plus 1 hour
    }

    public function initSession()
    {
        session_start();
    }

    public function destroySession(){
        $_SESSION = array();
        session_destroy();
    }

    public function isTmpSession() {
        return $_SESSION['tmp'];
    }

    public function isAdmin() {
        return $_SESSION['admin'];
    }

    public function getUsername() {
        return $_SESSION['username'];
    }

    /**
     * checks whether the provided csrf token matches the csrf token stored in the session
     * if it matches, it regenerates the token
     * 
     * @param $token the csrf token to compare
     * @return true|false true if token matches, false otherwise
     */
    public function checkCSRFToken($token){
        if(hash_equals($token, $_SESSION["csrf_token"])){
            $this->generateCSRFToken();
            return true;
        }
        return false;
    }

    private function generateCSRFToken(){
        $_SESSION["csrf_token"] = $this->generateToken();
    }

    public function getCSRFToken(){
        return $_SESSION["csrf_token"];
    }

    /**
     * checks whether the provided csrf token matches the csrf token stored in the session
     * if it matches, it regenerates the token
     * 
     * @param $token the csrf token to compare
     * @return true|false true if token matches, false otherwise
     */
    public function checkDownloadToken($token){
        if(isset($_SESSION["download_token"]) && hash_equals($token, $_SESSION["download_token"])){
            unset($_SESSION["download_token"]);
            return true;
        }
        return false;
    }

    public function generateDownloadToken(){
        $_SESSION["download_token"] = $this->generateToken();
        return $_SESSION["download_token"];
    }

    private function generateToken() {
        return bin2hex(random_bytes(32));
    }

}


?>