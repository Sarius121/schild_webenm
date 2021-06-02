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
        return copy($this->sourceFile, $this->targetFile);
    }

    public function saveFile(){
        return copy($this->targetFile, $this->sourceFile);
    }

    public function closeFile(){
        if($this->saveFile()){
            return unlink($this->targetFile);
        } else {
            return false;
        }
    }

}

?>