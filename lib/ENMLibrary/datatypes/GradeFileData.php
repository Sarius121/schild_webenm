<?php

namespace ENMLibrary\datatypes;

class GradeFileData {

    private $file;
    protected $data;
    private $tableColumns;
    private $dbTable;

    public function __construct($gradeFile, $tableColumns, $dbTable) {
        $this->file = $gradeFile;
        $this->tableColumns = $tableColumns;
        $this->dbTable = $dbTable;
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

    public function insertData($priKeyCol, $priKey, $col, $value){
        $this->file->insertData($this->dbTable, $priKeyCol, $priKey, $col, $value);
    }

}

?>