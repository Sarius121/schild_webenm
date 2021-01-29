<?php

namespace ENMLibrary;

use ENMLibrary\datatypes\ClassTeacherData;
use ENMLibrary\datatypes\ExamsData;
use ENMLibrary\datatypes\StudentGradesData;

class GradeFileDataHelper{

    private $gradeFile;

    private $studentGradesData;
    private $classTeacherData;
    private $examsData;

    public function __construct($gradeFile) {
        $this->gradeFile = $gradeFile;
        $this->studentGradesData = new StudentGradesData($this->gradeFile);
        $this->classTeacherData = new ClassTeacherData($this->gradeFile);
        $this->examsData = new ExamsData($this->gradeFile);
    }

    public function getDataObject($table){
        switch($table){
            case "GradeTable":
                return $this->studentGradesData;
                break;
            case "ClassTeacherTable":
                return $this->classTeacherData;
                break;
            case "ExamsTable":
                return $this->examsData;
                break;
        }
    }
}

?>