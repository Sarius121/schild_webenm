<?php

namespace ENMLibrary;

class BackupHandler {

    public function oldBackupExists($username){
        return file_exists(GRADE_FILES_DIRECTORY . $username . ".enz-old");
    }

    public function upload($file, $username, $password) {
        if(!$this->doSuperficialFileCheck($file)){
            return false;
        }

        $target_file = GRADE_FILES_DIRECTORY . $username . ".enz-new";
        if(!move_uploaded_file($file["tmp_name"], $target_file)){
            return false;
        }

        if(!$this->doDetailedFileCheck($target_file, $username, $password)){
            //delete uploaded file
            unlink($target_file);
            return false;
        }

        $current_file = GRADE_FILES_DIRECTORY . $username . ".enz";
        $old_target_file = GRADE_FILES_DIRECTORY . $username . ".enz-old";

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

    private function doDetailedFileCheck($target, $username, $password){
        //detailed file type check
        //is file zip archive encrypted with the right password?
        $tmp_grade_file = TMP_GRADE_FILES_DIRECTORY . "new-" . $username . ".enm";
        $zipArchive = new EncryptedZipArchive($target, $tmp_grade_file);
        if(!$zipArchive->open()){
            //file is not an encrypted zip archive or the password is wrong
            return false;
        }

        $success = false;

        //is file inside zip a grade file?
        $gradeFile = new GradeFile(basename($tmp_grade_file));
        if($gradeFile->openFile()){
            if($gradeFile->hasRequiredTables()){
                if($gradeFile->checkUser($password)){
                    $success = true;
                }
            } else {
                //some tables are missing
            }
        } else {
            //not a valid grade file
        }
        
        $gradeFile->close();
        $zipArchive->close(false);

        return $success;
    }

    /**
     * remove current grade file and rename old file
     */
    public function undoBackupRestore($username){
        if(!$this->oldBackupExists($username)){
            return false;
        }
        $current_file = GRADE_FILES_DIRECTORY . $username . ".enz";
        $old_file = GRADE_FILES_DIRECTORY . $username . ".enz-old";

        $current_safety_file = GRADE_FILES_DIRECTORY . $username . ".enz-safe";

        if(rename($current_file, $current_safety_file)){
            if(rename($old_file, $current_file)){
                unlink($current_safety_file);
                return true;
            } else {
                //undo rename
                rename($current_safety_file, $current_file);
            }
        }

        return false;
    }
}

?>