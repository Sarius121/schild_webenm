<?php

require_once("includes/utils.php");
require_once("config/ui-conf.php");

require_once("lib/ENMLibrary/LoggingHandler.php");

require_once("lib/MDBConnector/MDBDatabase.php");

// data source:
require_once("lib/ENMLibrary/datasource/DataSourceModule.php");
require_once("lib/ENMLibrary/datasource/modules/WebDavDataSource.php");
require_once("lib/ENMLibrary/datasource/modules/LocalFolderDataSource.php");
require_once("lib/ENMLibrary/datasource/DataSourceModuleHelper.php");

require_once("lib/ENMLibrary/RequestResponse.php");
require_once("lib/ENMLibrary/BackupHandler.php");
require_once("lib/ENMLibrary/LoginHandler.php");
require_once("lib/ENMLibrary/GradeFile.php");
require_once("lib/ENMLibrary/Modal.php");
require_once("lib/editablegrid/php/EditableGrid.php");

require_once("lib/ENMLibrary/GradeFileDataHelper.php");
require_once("lib/ENMLibrary/datatypes/StudentGradesData.php");
require_once("lib/ENMLibrary/datatypes/ClassTeacherData.php");
require_once("lib/ENMLibrary/datatypes/PhrasesData.php");
require_once("lib/ENMLibrary/datatypes/ExamsData.php");
require_once("lib/ENMLibrary/datatypes/GradesData.php");

?>