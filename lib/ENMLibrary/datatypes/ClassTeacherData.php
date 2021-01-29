<?php

namespace ENMLibrary\datatypes;

class ClassTeacherData {

    // Abschnitt_ID is the primary key in Kopfnoten but not in PSFachBem; probably Abschnitt_ID is unique in PSFachBem, too, but this is not proved!

    public const CLASS_TEACHER_COLUMNS = [["name" => "Abschnitt_ID", "label" => "Abschnitt_ID", "datatype" => "string", "editable" => false],
                                        ["name" => "Klasse", "label" => "Klasse", "datatype" => "string", "editable" => false], //Table: Kopfnoten
                                        ["name" => "Name", "label" => "Name", "datatype" => "string", "editable" => false],
                                        ["name" => "SumFehlstd", "label" => "FS", "datatype" => "string", "editable" => true], 
                                        ["name" => "SumFehlstdU", "label" => "uFS", "datatype" => "string", "editable" => true],
                                        ["name" => "hasASV", "label" => "ASV", "datatype" => "boolean", "editable" => true], //Table: PSFachBem
                                        ["name" => "hasAuE", "label" => "AuE", "datatype" => "boolean", "editable" => true], //??
                                        ["name" => "hasZeugnisBem", "label" => "ZB", "datatype" => "boolean", "editable" => true],
                                        ["name" => "ASV", "label" => "ASV", "datatype" => "string", "editable" => true],
                                        ["name" => "AuE", "label" => "AuE", "datatype" => "string", "editable" => true],
                                        ["name" => "ZeugnisBem", "label" => "ZB", "datatype" => "string", "editable" => true]
                                    ];

    public const COLUMNS_KOPFNOTEN = [["name" => "S_GUID"], ["name" => "Name"], ["name" => "Klasse"], ["name" => "SumFehlstd"], ["name" => "SumFehlstdU"]];
    public const COLUMNS_LEISTUNGSDATEN = [["name" => "S_GUID"], ["name" => "Abschnitt_ID"]];
    public const COLUMNS_PSFACHBEM = [["name" => "Abschnitt_ID"], ["name" => "ASV"], ["name" => "LELS"], ["name" => "ZeugnisBem"]];

    private $file;
    private $students; //cols: name, class, FS, uFS, ASV, AuE, ZeugnisBem, S_GUID, Abschnitt_ID, hasASV, hasAuE, hasZeugnisBem

    public function __construct($gradeFile) {
        $this->file = $gradeFile;
    }

    public function fetchClassTeacherTable(){
        //get students of class (class, name, FS, uFS, S_GUID)
        $this->fetchKopfnoten();

        //get Abschnitt_IDs of students by S_GUID
        $this->fetchAbschnittIDs();

        //get ASV and ZeugnisBem by Abschnitt_ID
        $this->fetchPSFachBem();
    }

    private function fetchKopfnoten(){
        $result = $this->file->fetchTableData("Kopfnoten", ClassTeacherData::COLUMNS_KOPFNOTEN);
        $this->students = $result;

        //TODO IstKlassenlehrer
        /*foreach($result as $student){
            $this->students[] = $student;
        }*/
    }

    private function fetchAbschnittIDs(){
        $filter = "S_GUID IN (SELECT S_GUID FROM Kopfnoten)";
        $result = $this->file->fetchTableData("SchuelerLeistungsDaten", ClassTeacherData::COLUMNS_LEISTUNGSDATEN, $filter, true);
        for($i = 0; $i < count($this->students); $i++){
            if(key_exists($this->students[$i]["S_GUID"], $result)){
                $row = $result[$this->students[$i]["S_GUID"]];
                foreach(ClassTeacherData::COLUMNS_LEISTUNGSDATEN as $col){
                    if(key_exists($col["name"], $row)){
                        $this->students[$i][$col["name"]] = $row[$col["name"]];
                    }
                }
            }
        }
    }

    private function fetchPSFachBem(){
        $result = $this->file->fetchTableData("SchuelerLD_PSFachBem", ClassTeacherData::COLUMNS_PSFACHBEM, null, true);

        for($i = 0; $i < count($this->students); $i++){
            if(key_exists($this->students[$i]["Abschnitt_ID"], $result)){
                $row = $result[$this->students[$i]["Abschnitt_ID"]];
                foreach(ClassTeacherData::COLUMNS_PSFACHBEM as $col){
                    if(key_exists($col["name"], $row)){
                        $newcol = $col;
                        if($col["name"] == "LELS"){
                            $newCol["name"] = "AuE";
                        }
                        $this->students[$i][$newcol["name"]] = $row[$col["name"]];
                        
                        switch($newcol["name"]){
                            case "ASV":
                            case "ZeugnisBem":
                            case "AuE":
                                if(strlen($row[$col["name"]]) > 0){
                                    $this->students[$i]["has" . $newcol["name"]] = true;
                                } else {
                                    $this->students[$i]["has" . $newcol["name"]] = false;
                                }
                                break;
                        }
                        
                    }
                }
            }
        }
    }

    public function getJSON(){
        $jsonArray = ["metadata" => ClassTeacherData::CLASS_TEACHER_COLUMNS];
        $jsonArray["data"] = array();
        
        if($this->students == null){
            $this->fetchClassTeacherTable();
        }
        
        for($i = 0; $i < count($this->students); $i++){
            $row = [];
            $row["id"] = $i;
            $row["values"] = $this->students[$i];
            $jsonArray["data"][] = $row;
        }

        //print_r(json_encode($jsonArray));
        return json_encode($jsonArray);
    }

}

?>