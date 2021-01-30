<?php

namespace ENMLibrary\datatypes;

class PhrasesData extends GradeFileData{

    public const PHRASES_COLUMNS = [["name" => "Kuerzel", "label" => "Ken.", "datatype" => "string", "editable" => false],
                                        ["name" => "Floskelgruppe", "label" => "Grp.", "datatype" => "string", "editable" => false],
                                        ["name" => "Floskelfach", "label" => "Fa.", "datatype" => "string", "editable" => false], 
                                        ["name" => "Floskeljahrgang", "label" => "Jg.", "datatype" => "string", "editable" => false],
                                        ["name" => "Floskelniveau", "label" => "Niv.", "datatype" => "string", "editable" => false],
                                        ["name" => "Floskeltext", "label" => "Text", "datatype" => "string", "editable" => false]
                                    ];

    //private $file;
    //private $phrases;

    public function __construct($gradeFile) {
        parent::__construct($gradeFile, PhrasesData::PHRASES_COLUMNS, "Floskeln", true); //readonly data
        //$this->file = $gradeFile;
    }

    public function fetchPhrases(){
        parent::fetchData();
        //$result = $this->file->fetchTableData("Floskeln", PhrasesData::PHRASES_COLUMNS);
        //$this->phrases = $result;
    }

    public function getJSON(){
        if($this->data == null){
            $this->fetchPhrases();
        }
        return parent::getJSON();
    }

    /**
     * not used anymore, filter is done by JavaScript
     * @deprecated
     */
    public function getFilteredJSON($group){
        $jsonArray = ["metadata" => PhrasesData::PHRASES_COLUMNS];
        $jsonArray["data"] = array();
        
        if($this->data == null){
            $this->fetchPhrases();
        }
        
        for($i = 0; $i < count($this->data); $i++){
            if($this->data[$i]["Floskelgruppe"] == $group){
                $row = [];
                $row["id"] = $i;
                $row["values"] = $this->data[$i];
                $jsonArray["data"][] = $row;
            }
        }

        //print_r(json_encode($jsonArray));
        return json_encode($jsonArray);
    }

}

?>