<?php

namespace ENMLibrary\datasource\modules;

use ENMLibrary\datasource\DataSourceModule;

class LocalFolderDataSource extends DataSourceModule {

    public static function getName(): string {
        return "Local Folder";
    }

    public function __construct() {
        parent::__construct();
    }

    public function openFile(): bool {
        if(file_exists($this->getSourceFile())){
            return copy($this->getSourceFile(), $this->getTargetFile());
        } else {
            return false;
        }
    }

    public function saveFile(): bool {
        return copy($this->getTargetFile(), $this->getSourceFile());
    }

    protected function findFilenameImpl(string $username): ?string {
        $found = glob(SOURCE_GRADE_FILES_DIRECTORY . $username . FILE_SUFFIX . "*.enz");
        if ($found == false || count($found) <= 0) {
            return null;
        }
        foreach ($found as $file) {
            //take first
            return basename($file, ".enz");
        }
        return null;
    }

    protected function getSourceFile(): string {
        return SOURCE_GRADE_FILES_DIRECTORY . parent::getSourceFile();
    }

    public function getFilesInfos(): array {
        $found = glob(SOURCE_GRADE_FILES_DIRECTORY . "*" . FILE_SUFFIX . "*.enz");
        if ($found == false || count($found) <= 0) {
            return array();
        }
        $result = array();
        foreach ($found as $file) {
            $fileInfos = array();
            $fileInfos["name"] = basename($file);
            $fileInfos["last-edit"] = filemtime($file);
            $result[] = $fileInfos;
        }
        return $result;
    }
}

?>