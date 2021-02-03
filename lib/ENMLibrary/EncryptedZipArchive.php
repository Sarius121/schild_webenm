<?php

namespace ENMLibrary;

use ZipArchive;

class EncryptedZipArchive{

    private $filename;
    private $gradeFilename;

    public function __construct($filename, $gradeFilename){
        $this->filename = $filename;
        $this->gradeFilename = $gradeFilename;
    }

    /**
     * unpack grade file to tmp-directory
     */
    public function open($password, $onlytry=false){
        $zip = new ZipArchive();
        if ($zip->open($this->filename) === true) {
            $zip->setPassword($password);
            $gradeFilenamePartArray = explode("/", $this->gradeFilename);
            $gradeFilenamePart = end($gradeFilenamePartArray);
            if(($gradeFile = $zip->getFromName($gradeFilenamePart))) {
                if(!$onlytry){
                    file_put_contents($this->gradeFilename, "$gradeFile");
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
        if(!file_exists($this->gradeFilename)){
            return false;
        }
        $zip = new ZipArchive();
        if ($zip->open($this->filename) === true) {
            $zip->setPassword($password);
            $gradeFilenamePartArray = explode("/", $this->gradeFilename);
            $gradeFilenamePart = end($gradeFilenamePartArray);
            if($zip->getFromName($gradeFilenamePart)) {
                //replaceFile() is only supported in PHP8 or higher
                $zip->deleteName($gradeFilenamePart);
                $zip->addFile($this->gradeFilename, $gradeFilenamePart);
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
        $this->saveChanges($password);
        unlink($this->gradeFilename);
    }
}

?>