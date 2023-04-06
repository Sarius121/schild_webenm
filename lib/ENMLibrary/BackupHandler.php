<?php

namespace ENMLibrary;

class BackupHandler {

    /**
     * check whether a backup file for a user exists
     * 
     * @param string $basename name of the file in the source directory (without file extension)
     * @return true|false true if an old backup exists, false otherwise
     */
    public function oldBackupExists($basename){
        return file_exists(GRADE_FILES_DIRECTORY . $basename . ".enz-old");
    }

    /**
     * upload file to the working directory -> needs to be saved to the source afterwards
     * 
     * @param string $file file which should be uploaded
     * @param string $password user's password
     * @param string $basename name of the file in the source directory (without file extension)
     * @param string $current_file the zip file which is currently used (in the working directory)
     */
    public function upload($file, $password, $basename, $current_file) {
        if(!$this->doSuperficialFileCheck($file)){
            return false;
        }

        // move file to working directory (with -new suffix)
        $target_file = GRADE_FILES_DIRECTORY . $basename . ".enz-new";
        if(!move_uploaded_file($file["tmp_name"], $target_file)){
            return false;
        }

        if(!$this->doDetailedFileCheck($target_file, $password, $basename)){
            // delete uploaded file if check fails
            unlink($target_file);
            return false;
        }

        // old files are not stored in source directory but the working directory
        $old_target_file = GRADE_FILES_DIRECTORY . $basename . ".enz-old";

        // add -old suffix to old file
        if(rename($current_file, $old_target_file)){
            if(rename($target_file, $current_file)){
                // upload accomplished
                return true;
            } else {
                // if an error occurs move old file back
                rename($old_target_file, $current_file);
                // unlink($old_target_file); -> TODO too risky?
            }
        }

        // delete uploaded file
        unlink($target_file);

        return false;
    }

    /**
     * check size and file extension (valid grade files without the enz-extension are not accepted)
     * 
     * @param $file file to check
     * @return true|false true if file passes checks, false otherwise
     */
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

    /**
     * check if file is an encrypted zip file, is accessable with zip-password and contains a file.
     * check if the file inside the archive is accessable with user's password and contains all required tables
     * 
     * @param string $target archive file to check
     * @param string $password user's password
     * @param string $basename name of the file in the source directory (without file extension)
     * @return true|false true if file passes checks, false otherwise
     */
    private function doDetailedFileCheck($target, $password, $basename){
        //is file zip archive encrypted with the right password?
        $tmp_grade_file = TMP_GRADE_FILES_DIRECTORY . "new-" . $basename . ".enm";
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
     * remove current grade file and move old file to current file
     * 
     * Notice that this is only done in the working directory -> needs to be saved to the source afterwards
     * 
     * @param string $basename name of the file in the source directory (without file extension)
     * @param string $current_file the zip file which is currently used (in the working directory)
     */
    public function undoBackupRestore($basename, $current_file){
        if(!$this->oldBackupExists($basename)){
            return false;
        }

        $old_file = GRADE_FILES_DIRECTORY . $basename . ".enz-old";

        $current_safety_file = GRADE_FILES_DIRECTORY . $basename . ".enz-safe";

        if(rename($current_file, $current_safety_file)){
            if(rename($old_file, $current_file)){
                unlink($current_safety_file);
                return true;
            } else {
                // undo rename
                rename($current_safety_file, $current_file);
            }
        }

        return false;
    }
}

?>