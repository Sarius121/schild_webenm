<?php

namespace ENMLibrary\datasource;

use ENMLibrary\datasource\modules\LocalFolderDataSource;
use ENMLibrary\datasource\modules\WebDavDataSource;

class DataSourceModuleHelper {

    public static function getModuleName(): string {
        return DATA_SOURCE_MODULE;
    }

    public static function createModule(): DataSourceModule {
        switch(DATA_SOURCE_MODULE) {
            case LocalFolderDataSource::getName():
                return new LocalFolderDataSource();
            case WebDavDataSource::getName():
                return new WebDavDataSource();
            default:
                return null;
        }
    }
}

?>