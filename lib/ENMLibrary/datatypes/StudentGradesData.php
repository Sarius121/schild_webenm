<?php

namespace ENMLibrary\datatypes;

require_once("GradeFileData.php");

class StudentGradesData extends GradeFileData{

    public const STUDENT_GRADES_COLUMNS = [["name" => "Leistung_ID", "label" => "Leistung_ID", "datatype" => "string", "editable" => false],
                            ["name" => "Klasse", "label" => "Klasse", "datatype" => "string", "editable" => false],
                            ["name" => "Name", "label" => "Name", "datatype" => "string", "editable" => false],
                            ["name" => "FachBez", "label" => "Fach", "datatype" => "string", "editable" => false],
                            /*["name" => "KursartKrz", "label" => "Art", "datatype" => "string", "editable" => false],*/
                            ["name" => "KurzBez", "label" => "Kurs", "datatype" => "string", "editable" => false],
                            ["name" => "NotenKrz", "label" => "Note", "datatype" => "string", "editable" => true]
                            /*["name" => "Fehlstd", "label" => "FS", "datatype" => "integer", "editable" => true],
                            ["name" => "uFehlstd", "label" => "uFS", "datatype" => "integer", "editable" => true],
["name" => "Warnung", "label" => "Mahnung", "datatype" => "string", "editable" => true, "values" => ["-" => "keine Mahnung","+" =>"Mahnung"]]*/];

    public function __construct($gradeFile) {
        $columns = StudentGradesData::STUDENT_GRADES_COLUMNS;
        //add optional columns
        $insertionCount = 0;
        if(SHOW_DATA_STUDENT_KURSART){
            array_splice($columns, 4, 0, [["name" => "KursartKrz", "label" => "Art", "datatype" => "string", "editable" => false]]);
            $insertionCount++;
        }
        if(SHOW_DATA_STUDENT_GRADES_FS){
            array_splice($columns, 6 + $insertionCount, 0, [["name" => "Fehlstd", "label" => "FS", "datatype" => "integer", "editable" => true]]);
            $insertionCount++;
        }
        if(SHOW_DATA_STUDENT_GRADES_uFS){
            array_splice($columns, 6 + $insertionCount, 0, [["name" => "uFehlstd", "label" => "uFS", "datatype" => "integer", "editable" => true]]);
            $insertionCount++;
        }
        if(SHOW_DATA_STUDENT_GRADES_MAHNUNG){
            array_splice($columns, 6 + $insertionCount, 0, [["name" => "Warnung", "label" => "Mahnung", "datatype" => "string", "editable" => true, "values" => ["-" => "keine Mahnung","+" =>"Mahnung"]]]);
        }

        parent::__construct($gradeFile, $columns, "SchuelerLeistungsdaten");
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
        $response = parent::insertData($priKeyCol, $priKey, $col, $value); //TODO is value valid
        if($response == false){return false;};
        parent::insertData($priKeyCol, $priKey, "Modifiziert", true);
        if($col == "NotenKrz"){
            $points = $this->getPointsOfGrade($value);
            if($points != false){
                parent::insertData($priKeyCol, $priKey, "Punkte", $points);
            }
        }
    }

    private function getPointsOfGrade($grade){
        $result = $this->file->fetchTableData("Noten", [["name" => "Punkte"]], "Krz = '" . $grade . "'");
        if(count($result) > 0){
            return $result[0]["Punkte"];
        } else {
            return false;
        }
    }
}

?>