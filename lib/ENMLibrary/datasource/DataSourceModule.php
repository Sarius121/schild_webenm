<?php

namespace ENMLibrary\datasource;

abstract class DataSourceModule {

    public abstract static function getName(): string;

    private ?string $filename = null;
    private ?string $targetFile = null;

    protected function __construct() {
    }

    public function equals(DataSourceModule $module): bool {
        return $this::getName() == $module::getName();
    }

    /**
     * set target file
     * 
     * Notice: If this function is not called before operating on the file, there will be errors.
     * 
     * @param string $targetFile path to target file
     */
    public function setTargetFile(string $targetFile) {
        $this->targetFile = $targetFile;
    }

    /**
     * opens the file: after calling this function, the file should be in the working directory
     * 
     * @return bool false if an error occurs, true otherwise
     */
    public abstract function openFile(): bool;

    /**
     * saves the file: after calling this function, the file is saved in the source but not deleted in the working directory
     * 
     * @return bool false if an error occurs, true otherwise
     */
    public abstract function saveFile(): bool;

    /**
     * closes the file i.e. deletes the file in the working directory
     * 
     * @param bool $saveChanges if true, the file is saved and then deleted
     * @return bool false if an error occurs, true otherwise
     */
    public function closeFile(bool $saveChanges = true): bool {
        if(!$saveChanges || $this->saveFile()){
            return unlink($this->targetFile);
        } else {
            return false;
        }
    }

    /**
     * tries to find a file which belongs to the given user
     * 
     * pattern: $username . FILE_SUFFIX . * . ".enz" (. is concatenation here)
     * 
     * Notice: If the filename has been found before, the user won't be looked up again.
     * 
     * Notice: If this function is not called before operating on the file, there will be errors.
     * 
     * @param string $username the user for whom the file is searched
     * @return string the correct filename of the file (without extension), null if no file exists
     */
    public function findFilename(string $username): ?string {
        if (is_null($this->filename)) {
            return ($this->filename = $this->findFilenameImpl($username));
        } else {
            return $this->filename;
        }
    }

    protected abstract function findFilenameImpl(string $username): ?string;

    /**
     * returns an array containing information about a file
     * 
     * for each element the following keys are defined:
     * - user: username
     * - file: filename
     * - last-edit: date of the last edit
     * 
     * @return array infos about all available files
     */
    public abstract function getFilesInfos(): array;

    protected function getSourceFile(): string {
        return $this->filename . ".enz";
    }

    public function setSourceFile(string $sourceFile) {
        $this->filename = basename($sourceFile, ".enz");
    }

    protected function getTargetFile(): string {
        return $this->targetFile;
    }

    /**
     * returns some information provided by the module as key value pairs
     */
    public function getModuleInformation(): array {
        return [];
    }

    /**
     * helper method to get username by filename
     * 
     * @param string $filename filename to extract username from
     * @return string extracted username
     */
    protected function getUsernameByFilename(string $filename): string {
        $matches = array();
        preg_match("/^(.*)" . preg_quote(FILE_SUFFIX) . ".*$/", basename($filename, ".enz"), $matches);
        if (isset($matches[1])) {
            return $matches[1];
        } else {
            return "";
        }
    }
}

?>