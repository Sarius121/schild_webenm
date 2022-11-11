<?php

namespace ENMLibrary\datasource;

abstract class DataSourceModule {

    public abstract static function getName(): string;

    protected function __construct()
    {
    }
    
    public function getSettingsHTML(): string {
        return "";
    }

    public function equals(DataSourceModule $module): bool {
        return $this::getName() == $module::getName();
    }
}

?>