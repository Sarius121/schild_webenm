<?php
namespace ENMLibrary;

require_once("constants.php");

use Exception;
use ErrorException;
use PDO;
use stdClass;

class GradeFile {

    /*public static const COLUMNS = ["Klasse" => "Klasse", "Name" => "Name", "FachBez" => "Fach", "KursartKrz" => "Art", "KursartBez" => "Kurs", 
                                    "Kurs_ID" => "Nr.", "NotenKrz" => "Note", "Punkte" => "Pkte.", "Fehlstd" => "FS", "uFehlstd" => "uFS", "Warnung" => "M", 
                                    "Zuweisung" => "Zuw.", "FachBez" => "Fachbezogene Bemerkung", "Fx" => "Fx.", "KF" => "KF", "Komp" => "Komp.", "Schueler_ID" => "ID", 
                                    "JahrgangIntern" => "JG", "AOSF" => "AOSF", "ZieldifferentesLernen" => "ZdL", "Sortierung" => "Sort."];*/
    /*public const COLUMNS = ["Klasse" => "Klasse", "Name" => "Name", "FachBez" => "Fach", "KursartKrz" => "Art", "KurzBez" => "Kurs", "NotenKrz" => "Note", "Warnung" => "Mahnung"];*/

    public const COLUMNS = [["name" => "Klasse", "label" => "Klasse", "datatype" => "string", "editable" => false],
                            ["name" => "Name", "label" => "Name", "datatype" => "string", "editable" => false],
                            ["name" => "FachBez", "label" => "Fach", "datatype" => "string", "editable" => false],
                            ["name" => "KursartKrz", "label" => "Art", "datatype" => "string", "editable" => false],
                            ["name" => "KurzBez", "label" => "Kurs", "datatype" => "string", "editable" => false],
                            ["name" => "NotenKrz", "label" => "Note", "datatype" => "string", "editable" => true],
                            ["name" => "Warnung", "label" => "Mahnung", "datatype" => "string", "editable" => true, "values" => ["-" => "keine Mahnung","+" =>"Mahnung"]
                            ]];

    public const GRADE_COLUMNS = [["name" => "Krz", "label" => "Krz.", "size" => "-1"],
                            ["name" => "Bezeichnung", "label" => "Bezeichnung", "size" => "-4"],
                            ["name" => "Zeugnisnotenbez", "label" => "Zeugnisbez.", "size" => ""]];

    private $filename;
    private $db;
    private $error;

    public function __construct($filename) {
        $this->filename = $filename;
    }

    public function openFile($password=DEFAULT_DB_PASSWORD){ 
        if(!file_exists($this->filename)){
            $this->error = "File not found";
            return false;
        }
        /*$options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES => false
        ];*/
        set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext) {
            // error was suppressed with the @-operator
            if (0 === error_reporting()) {
                return false;
            }
        
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        });
        try{
            $this->db = odbc_connect("DRIVER={Microsoft Access Driver (*.mdb)};charset=UTF-8; DBQ=" . $this->filename . ";", "", $password);
        } catch(Exception $e){
            $this->error = $e->getMessage();
            return false;
        }

        return true;
        //print_r($this->db->errorInfo());
        
    }

    public function getTable(){
        $table = [];

        $sql = 'SELECT * FROM SchuelerLeistungsDaten';
        $result = odbc_exec($this->db, $sql);
        while($dbRow = odbc_fetch_array($result)){
            $row = [];
            for($i = 0; $i < count(GradeFile::COLUMNS); $i++){
                //$row[array_values(GradeFile::COLUMNS)[$i]] = $dbRow[array_keys(GradeFile::COLUMNS)[$i]];
                $row[GradeFile::COLUMNS[$i]["name"]] = utf8_encode($dbRow[GradeFile::COLUMNS[$i]["name"]]);
            }
            $table[] = $row;
        }
        //print_r($table);
        return $table;

        //print_r(odbc_result_all($result));
    }

    public function getJSONTable(){
        $jsonArray = ["metadata" => GradeFile::COLUMNS];
        $jsonArray["data"] = array();
        $table = $this->getTable();
        
        for($i = 0; $i < count($table); $i++){
            $row = [];
            $row["id"] = $i;
            $row["values"] = $table[$i];
            $jsonArray["data"][] = $row;
        }

        //print_r(json_encode($jsonArray));
        return json_encode($jsonArray);
    }

    public function getGrades(){
        return $this->fetchTableData("Noten", GradeFile::GRADE_COLUMNS);
    }

    public function fetchTableData($tablename, $columns){
        $table = [];

        $sql = 'SELECT * FROM ' . $tablename . ';';
        $result = odbc_exec($this->db, $sql);
        while($dbRow = odbc_fetch_array($result)){
            $row = [];
            for($i = 0; $i < count($columns); $i++){
                //$row[array_values(GradeFile::COLUMNS)[$i]] = $dbRow[array_keys(GradeFile::COLUMNS)[$i]];
                $row[$columns[$i]["name"]] = utf8_encode($dbRow[$columns[$i]["name"]]);
            }
            $table[] = $row;
        }
        return $table;
    }

    public function getTables(){
        $result = odbc_tables($this->db);

        echo '<div id="top">..</div><table border="1" cellpadding="5"><tr>';

        $tblRow = 1;
        while (odbc_fetch_row($result)){
            if(odbc_result($result,"TABLE_TYPE")=="TABLE"){
                $tableName = odbc_result($result,"TABLE_NAME");
                echo '<tr><td>' . $tblRow . '</td><td><a href="#' . $tableName . '">' . $tableName . '</a></td></tr>';
                $tblRow++;
            }  
        }
        echo '</table><hr>';
    }

    public function getTableData($tablename){
        $sql = "select * from " . $tablename . ";";
        $result = $this->db->query($sql);
        print_r($result->fetchAll());
    }

    public function close(){
        odbc_close($this->db);
    }

    public function getError(){
        return $this->error;
    }

}

?>