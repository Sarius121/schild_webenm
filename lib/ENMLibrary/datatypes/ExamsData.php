<?php

namespace ENMLibrary\datatypes;

class ExamsData extends GradeFileData{

    public const EXAMS_COLUMNS = [["name" => "ID", "label" => "ID", "datatype" => "string", "editable" => false],
                                        ["name" => "Klasse", "label" => "Klasse", "datatype" => "string", "editable" => false], //Leistungsdaten
                                        ["name" => "Name", "label" => "Name", "datatype" => "string", "editable" => false],
                                        ["name" => "FachKrz", "label" => "Fach", "datatype" => "string", "editable" => false], //SchuelerBKFaecher
                                        ["name" => "Fachlehrer", "label" => "Lehrkraft", "datatype" => "string", "editable" => false],
                                        ["name" => "Vornote", "label" => "Vornote", "datatype" => "string", "editable" => true],
                                        ["name" => "NoteSchriftlich", "label" => "Note schr. Prüf.", "datatype" => "string", "editable" => true],
                                        ["name" => "MdlPruefung", "label" => "Mdl. Prüf.", "datatype" => "boolean", "editable" => true],
                                        ["name" => "MdlPruefungFW", "label" => "Mdl. freiw. Prüf.", "datatype" => "boolean", "editable" => true],
                                        ["name" => "NoteMuendlich", "label" => "Note mdl. Prüf.", "datatype" => "string", "editable" => true],
                                        ["name" => "NoteAbschluss", "label" => "Abschlussnote", "datatype" => "string", "editable" => true]
                                    ];

    public const COLUMNS_BKFAECHER = [["name" => "ID"], ["name" => "Schueler_ID"], ["name" => "FachKrz"], ["name" => "Fachlehrer"], ["name" => "Vornote"], ["name" => "NoteSchriftlich"], 
                                        ["name" => "MdlPruefung"], ["name" => "MdlPruefungFW"], ["name" => "NoteMuendlich"], ["name" => "NoteAbschluss"]];
    public const COLUMNS_LEISTUNGSDATEN = [["name" => "Schueler_ID"], ["name" => "Klasse"], ["name" => "Name"]];

    //private $file;
    //private $exams;

    public function __construct($gradeFile) {
        parent::__construct($gradeFile, ExamsData::EXAMS_COLUMNS, "SchuelerBKFaecher");
        //$this->file = $gradeFile;
    }

    public function fetchExams(){
        $this->fetchExamGrades();
        $this->fetchStudentData();
    }

    public function fetchExamGrades(){
        $result = $this->file->fetchTableData("SchuelerBKFaecher", ExamsData::COLUMNS_BKFAECHER);
        for($i = 0; $i < count($result); $i++){
            $result[$i]["MdlPruefung"] = ($result[$i]["MdlPruefung"] == "+");
            $result[$i]["MdlPruefungFW"] = ($result[$i]["MdlPruefung"] == "+");
        }
        $this->data = $result;
    }

    public function fetchStudentData(){
        $filter = "Schueler_ID IN (SELECT Schueler_ID FROM SchuelerBKFaecher)";
        $result = $this->file->fetchTableData("SchuelerLeistungsDaten", ExamsData::COLUMNS_LEISTUNGSDATEN, $filter, true);
        for($i = 0; $i < count($this->data); $i++){
            if(key_exists($this->data[$i]["Schueler_ID"], $result)){
                $row = $result[$this->data[$i]["Schueler_ID"]];
                foreach(ExamsData::COLUMNS_LEISTUNGSDATEN as $col){
                    if(key_exists($col["name"], $row)){
                        $this->data[$i][$col["name"]] = $row[$col["name"]];
                    }
                }
            }
        }
    }

    public function getJSON(){
        if($this->data == null){
            $this->fetchExams();
        }
        return parent::getJSON();
    }

    public function insertData($priKeyCol, $priKey, $col, $value)
    {
        $editedValue = $value;
        if($col == "MdlPruefung" || $col == "MdlPruefungFW"){
            if($value == true){
                $editedValue = "+";
            } else if($value == false) {
                $editedValue = "-";
            } else {
                return false;
            }
        }

        return parent::insertData($priKeyCol, $priKey, $col, $editedValue);
    }

}

?>