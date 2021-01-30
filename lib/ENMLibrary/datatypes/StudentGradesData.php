<?php

namespace ENMLibrary\datatypes;

require_once("GradeFileData.php");

class StudentGradesData extends GradeFileData{

    public const STUDENT_GRADES_COLUMNS = [["name" => "Leistung_ID", "label" => "Leistung_ID", "datatype" => "string", "editable" => false],
                            ["name" => "Klasse", "label" => "Klasse", "datatype" => "string", "editable" => false],
                            ["name" => "Name", "label" => "Name", "datatype" => "string", "editable" => false],
                            ["name" => "FachBez", "label" => "Fach", "datatype" => "string", "editable" => false],
                            ["name" => "KursartKrz", "label" => "Art", "datatype" => "string", "editable" => false],
                            ["name" => "KurzBez", "label" => "Kurs", "datatype" => "string", "editable" => false],
                            ["name" => "NotenKrz", "label" => "Note", "datatype" => "string", "editable" => true],
                            ["name" => "Warnung", "label" => "Mahnung", "datatype" => "string", "editable" => true, "values" => ["-" => "keine Mahnung","+" =>"Mahnung"]]];

    public function __construct($gradeFile) {
        parent::__construct($gradeFile, StudentGradesData::STUDENT_GRADES_COLUMNS, "SchuelerLeistungsdaten");
    }

    public function fetchGradesData()
    {
        $this->fetchData("SchuelerLeistungsDaten");
    }

    public function getJSON()
    {
        if($this->data == null){
            $this->fetchGradesData();
        }
        return parent::getJSON();
    }

    public function insertData($priKeyCol, $priKey, $col, $value)
    {
        parent::insertData($priKeyCol, $priKey, $col, $value); //TODO is value valid
        parent::insertData($priKeyCol, $priKey, "Modifiziert", true);
        if($col == "NotenKrz"){
            parent::insertData($priKeyCol, $priKey, "Punkte", $this->getPointsOfGrade($value));
        }
    }

    private function getPointsOfGrade($grade){
        $result = $this->file->fetchTableData("Noten", [["name" => "Punkte"]], "Krz = '" . $grade . "'");
        print_r($result);
        return $result[0]["Punkte"];
    }
}

?>