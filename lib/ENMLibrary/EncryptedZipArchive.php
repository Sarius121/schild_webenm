<?php

namespace ENMLibrary;

use ZipArchive;

class EncryptedZipArchive{

    private $filename;
    private $internalFilename;
    private $tmpFilename;

    public function __construct($filename, $internalFilename, $tmpFilename){
        $this->filename = $filename;
        $this->internalFilename = $internalFilename;
        $this->tmpFilename = $tmpFilename;
    }

    /**
     * unpack grade file to tmp-directory
     */
    public function open($password, $onlytry=false){
        $zip = new ZipArchive();
        if ($zip->open($this->filename) === true) {
            $zip->setPassword($password);
            if(($gradeFile = $zip->getFromName($this->internalFilename))) {
                if(!$onlytry){
                    file_put_contents($this->tmpFilename, "$gradeFile");
                }
            } else {
                $zip->close();
                return false;
            }
            $zip->close();
            return true;
        } else {
            return false;
        }
    }

    public function checkPassword($password){
        return $this->open($password, true);
    }

    public function saveChanges($password){
        if(!file_exists($this->tmpFilename)){
            return false;
        }
        $zip = new ZipArchive();
        if ($zip->open($this->filename) === true) {
            $zip->setPassword($password);
            if($zip->getFromName($this->internalFilename)) {
                //replaceFile() is only supported in PHP8 or higher
                $zip->deleteName($this->internalFilename);
                $zip->addFile($this->tmpFilename, $this->internalFilename);
            } else {
                $zip->close();
                return false;
            }
            $zip->close();
            return true;
        } else {
            return false;
        }
    }

    /**
     * pack temporarily stored grade file as zip archive
     */
    public function close($password){
        $success = $this->saveChanges($password);
        if($success){
            unlink($this->tmpFilename);
        }
        return $success;
    }
}

?>