<?php

namespace ENMLibrary;

class FileUploader {

    private $file;
    private $username;
    private $password;

    public function __construct($file, $username, $password)
    {
        $this->file = $file;
        $this->username = $username;
        $this->password = $password;
    }

    public function upload() {
        if(!$this->doSuperficialFileCheck($this->file)){
            return false;
        }

        $target_file = GRADE_FILES_DIRECTORY . $this->username . ".enz-new";
        if(!move_uploaded_file($this->file["tmp_name"], $target_file)){
            return false;
        }
        
        if(!$this->doDetailedFileCheck($target_file)){
            //delete uploaded file
            unlink($target_file);
            return false;
        }

        $current_file = GRADE_FILES_DIRECTORY . $this->username . ".enz";
        $old_target_file = GRADE_FILES_DIRECTORY . $this->username . ".enz-old";

        if(rename($current_file, $old_target_file)){
            if(rename($target_file, $current_file)){
                //upload accomplished
                return true;
            } else {
                //if an error occurs move old file back
                rename($old_target_file, $current_file);
                //unlink($old_target_file); -> TODO too risky?
            }
        }

        unlink($target_file);

        return false;
    }

    private function doSuperficialFileCheck($file){
        //check size
        if($file["size"] > 10000000){
            //file to big
            return false;
        }

        //check type
        $fileType = strtolower(pathinfo(basename($file["name"]), PATHINFO_EXTENSION));
        if($fileType != "enz"){
            //wrong file type
            return false;
        }
        return true;
    }

    private function doDetailedFileCheck($target){
        //detailed file type check
        //is file zip archive encrypted with the right password?
        $tmp_grade_file = GRADE_FILES_DIRECTORY . $this->username . ".enm-new";
        $zipArchive = new EncryptedZipArchive($target, $tmp_grade_file);
        if(!$zipArchive->open($this->password)){
            //file is not an encrypted zip archive or the password is wrong
            return false;
        }

        //is file inside zip a grade file?
        $gradeFile = new GradeFile($tmp_grade_file);
        if(!$gradeFile->openFile()){
            //not a valid grade file
            return false;
        }
        if(!$gradeFile->hasRequiredTables()){
            //some tables are missing
            return false;
        }
        $gradeFile->close();
        $zipArchive->close($this->password, false);

        return true;
    }
}

?>