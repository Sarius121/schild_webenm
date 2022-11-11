<?php

namespace ENMLibrary\datasource;

use ENMLibrary\datasource\modules\WebDavDataSource;

class DataSourceModuleHelper {

    public static function getAvailableModules() {
        return [ WebDavDataSource::getName() ];
    }

    public static function createModule(string $module): DataSourceModule {
        if (!in_array($module, DataSourceModuleHelper::getAvailableModules())) {
            return null;
        }
        switch($module) {
            case WebDavDataSource::getName():
                return new WebDavDataSource();
            default:
                return null;
        }
    }

    private DataSourceModule $module;

    public function __construct(string $module)
    {
        $this->module = DataSourceModuleHelper::createModule($module);
    }

    public function getModule(): DataSourceModule {
        return $this->module;
    }
}

?>