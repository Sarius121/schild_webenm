<?php

namespace ENMLibrary\datasource\modules;

use ENMLibrary\datasource\DataSourceModule;

class WebDavDataSource extends DataSourceModule {

    public static function getName(): string {
        return "WebDav";
    }

    public function __construct()
    {
        parent::__construct();
    }
}

?>