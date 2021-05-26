<?php

namespace ENMLibrary;

use ZipArchive;

class EncryptedZipArchive{

    //zip filename with relative path (.enz)
    private $zipfilename;
    //filename inside the zip archive
    private $internalFilename;
    //database filename (.enm)
    private $tmpFilename;

    public function __construct($zipfilename, $dbfilename){
        $this->zipfilename = $zipfilename;
        $this->internalFilename = basename($zipfilename, "enz") . "enm";
        $this->tmpFilename = $dbfilename;
    }

    /**
     * unpack grade file to tmp-directory
     */
    public function open($password, $onlytry=false){
        $zip = new ZipArchive();
        if ($zip->open($this->zipfilename) === true) {
            $zip->setPassword($password);
            //if count bigger than 1 -> file not valid
            if($zip->count() > 1){
                return false;
            }
            if(($gradeFile = $zip->getFromIndex(0))) {
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
        if ($zip->open($this->zipfilename) === true) {
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
    public function close($password, $saveChanges = true){
        $success = true;
        if($saveChanges){
            $success = $this->saveChanges($password);
        }
        if($success){
            unlink($this->tmpFilename);
        }
        return $success;
    }
}

?>