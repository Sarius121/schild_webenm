<?php

namespace ENMLibrary\datatypes;

class ExamsData {

    public const EXAMS_COLUMNS = [["name" => "Klasse", "label" => "Klasse", "datatype" => "string", "editable" => false], //Leistungsdaten
                                        ["name" => "Name", "label" => "Name", "datatype" => "string", "editable" => false],
                                        ["name" => "FachKrz", "label" => "Fach", "datatype" => "string", "editable" => false], //SchuelerBKFaecher
                                        ["name" => "Fachlehrer", "label" => "Lehrkraft", "datatype" => "string", "editable" => false],
                                        ["name" => "Vornote", "label" => "Vornote", "datatype" => "string", "editable" => true],
                                        ["name" => "NoteSchriftlich", "label" => "Note schr. Prüf.", "datatype" => "string", "editable" => true],
                                        ["name" => "MdlPruefung", "label" => "Mdl. Prüf.", "datatype" => "string", "editable" => false],
                                        ["name" => "MdlPruefungFW", "label" => "Mdl. freiw. Prüf.", "datatype" => "string", "editable" => false],
                                        ["name" => "NoteMuendlich", "label" => "Note mdl. Prüf.", "datatype" => "string", "editable" => false],
                                        ["name" => "NoteAbschluss", "label" => "Abschlussnote", "datatype" => "string", "editable" => false]
                                    ];

    public const COLUMNS_BKFAECHER = [["name" => "Schueler_ID"], ["name" => "FachKrz"], ["name" => "Fachlehrer"], ["name" => "Vornote"], ["name" => "NoteSchriftlich"], 
                                        ["name" => "MdlPruefung"], ["name" => "MdlPruefungFW"], ["name" => "NoteMuendlich"], ["name" => "NoteAbschluss"]];
    public const COLUMNS_LEISTUNGSDATEN = [["name" => "Schueler_ID"], ["name" => "Klasse"], ["name" => "Name"]];

    private $file;
    private $exams;

    public function __construct($gradeFile) {
        $this->file = $gradeFile;
    }

    public function fetchExams(){
        $this->fetchExamGrades();
        $this->fetchStudentData();
    }

    public function fetchExamGrades(){
        $result = $this->file->fetchTableData("SchuelerBKFaecher", ExamsData::COLUMNS_BKFAECHER);
        $this->exams = $result;
    }

    public function fetchStudentData(){
        $filter = "Schueler_ID IN (SELECT Schueler_ID FROM SchuelerBKFaecher)";
        $result = $this->file->fetchTableData("SchuelerLeistungsDaten", ExamsData::COLUMNS_LEISTUNGSDATEN, $filter);
        for($i = 0; $i < count($this->exams); $i++){
            if(key_exists($this->exams[$i]["Schueler_ID"], $result)){
                $row = $result[$this->exams[$i]["Schueler_ID"]];
                foreach(ExamsData::COLUMNS_LEISTUNGSDATEN as $col){
                    if(key_exists($col["name"], $row)){
                        $this->exams[$i][$col["name"]] = $row[$col["name"]];
                    }
                }
            }
        }
    }

    public function getJSON(){
        $jsonArray = ["metadata" => ExamsData::EXAMS_COLUMNS];
        $jsonArray["data"] = array();
        
        if($this->exams == null){
            $this->fetchExams();
        }
        
        for($i = 0; $i < count($this->exams); $i++){
            $row = [];
            $row["id"] = $i;
            $row["values"] = $this->exams[$i];
            $jsonArray["data"][] = $row;
        }

        return json_encode($jsonArray);
    }

}

?>