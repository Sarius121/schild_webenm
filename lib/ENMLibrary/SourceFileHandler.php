<?php

namespace ENMLibrary;

class SourceFileHandler {

    private $sourceFile;
    private $targetFile;

    public function __construct($sourceFile, $targetFile)
    {
        $this->sourceFile = $sourceFile;
        $this->targetFile = $targetFile;   
    }

    public function openFile(){
        if(file_exists($this->sourceFile)){
            return copy($this->sourceFile, $this->targetFile);
        } else {
            return false;
        }
    }

    public function saveFile(){
        return copy($this->targetFile, $this->sourceFile);
    }

    public function closeFile($saveChanges = true){
        if(!$saveChanges || $this->saveFile()){
            return unlink($this->targetFile);
        } else {
            return false;
        }
    }

}

?>