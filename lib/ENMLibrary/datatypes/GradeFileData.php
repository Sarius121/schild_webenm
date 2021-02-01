<?php

namespace ENMLibrary\datatypes;

class GradeFileData {

    protected $file;
    protected $data;
    private $tableColumns;
    private $dbTable;

    private $readonly;

    public function __construct($gradeFile, $tableColumns, $dbTable, $readonly = false) {
        $this->file = $gradeFile;
        $this->tableColumns = $tableColumns;
        $this->dbTable = $dbTable;
        $this->readonly = $readonly;
    }

    protected function fetchData($table = null, $columns = null){
        if($columns == null){
            $columns = $this->tableColumns;
        }
        if($table == null){
            $table = $this->dbTable;
        }
        $result = $this->file->fetchTableData($table, $columns);
        $this->data = $result;
    }

    public function getJSON(){
        $jsonArray = ["metadata" => $this->tableColumns];
        $jsonArray["data"] = array();
        
        if($this->data == null){
            return false;
        }
        
        for($i = 0; $i < count($this->data); $i++){
            $row = [];
            $row["id"] = $i;
            $row["values"] = $this->data[$i];
            $jsonArray["data"][] = $row;
        }

        return json_encode($jsonArray);
    }

    protected function setDBTable($table){
        $this->dbTable = $table;
    }

    public function insertData($priKeyCol, $priKey, $col, $value){
        if($this->readonly){
            return false;
        }
        //TODO is col editable
        return $this->file->insertData($this->dbTable, $priKeyCol, $priKey, $col, $value);
    }

}

?>