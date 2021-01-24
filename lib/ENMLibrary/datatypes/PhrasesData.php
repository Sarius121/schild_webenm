<?php

namespace ENMLibrary\datatypes;

class PhrasesData {

    public const PHRASES_COLUMNS = [["name" => "Kuerzel", "label" => "Ken.", "datatype" => "string", "editable" => false],
                                        ["name" => "Floskelgruppe", "label" => "Grp.", "datatype" => "string", "editable" => false],
                                        ["name" => "Floskelfach", "label" => "Fa.", "datatype" => "string", "editable" => false], 
                                        ["name" => "Floskeljahrgang", "label" => "Jg.", "datatype" => "string", "editable" => false],
                                        ["name" => "Floskelniveau", "label" => "Niv.", "datatype" => "string", "editable" => false],
                                        ["name" => "Floskeltext", "label" => "Text", "datatype" => "string", "editable" => false]
                                    ];

    private $file;
    private $phrases;

    public function __construct($gradeFile) {
        $this->file = $gradeFile;
    }

    public function fetchPhrases(){
        $result = $this->file->fetchTableData("Floskeln", PhrasesData::PHRASES_COLUMNS);
        $this->phrases = $result;
    }

    public function getJSON(){
        $jsonArray = ["metadata" => PhrasesData::PHRASES_COLUMNS];
        $jsonArray["data"] = array();
        
        if($this->phrases == null){
            $this->fetchPhrases();
        }
        
        for($i = 0; $i < count($this->phrases); $i++){
            $row = [];
            $row["id"] = $i;
            $row["values"] = $this->phrases[$i];
            $jsonArray["data"][] = $row;
        }

        //print_r(json_encode($jsonArray));
        return json_encode($jsonArray);
    }

    public function getFilteredJSON($group){
        $jsonArray = ["metadata" => PhrasesData::PHRASES_COLUMNS];
        $jsonArray["data"] = array();
        
        if($this->phrases == null){
            $this->fetchPhrases();
        }
        
        for($i = 0; $i < count($this->phrases); $i++){
            if($this->phrases[$i]["Floskelgruppe"] == $group){
                $row = [];
                $row["id"] = $i;
                $row["values"] = $this->phrases[$i];
                $jsonArray["data"][] = $row;
            }
        }

        //print_r(json_encode($jsonArray));
        return json_encode($jsonArray);
    }

}

?>