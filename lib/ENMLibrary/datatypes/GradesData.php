<?php 

namespace ENMLibrary\datatypes;

class GradesData extends GradeFileData{

    public const GRADES_COLUMNS = [["name" => "Krz", "label" => "Krz.", "size" => "-1"],
                            ["name" => "Bezeichnung", "label" => "Bezeichnung", "size" => "-4"],
                            ["name" => "Zeugnisnotenbez", "label" => "Zeugnisbez.", "size" => ""],
                            ["name" => "Art", "label" => "Art", "size" => " hidden"]];

    public function __construct($gradeFile) {
        parent::__construct($gradeFile, GradesData::GRADES_COLUMNS, "Noten");
    }

    public function fetchGradesData()
    {
        $this->fetchData("Noten");
    }

    public function getGradesArray(){
        if($this->data == null){
            $this->fetchGradesData();
        }
        return $this->data;
    }
}

?>