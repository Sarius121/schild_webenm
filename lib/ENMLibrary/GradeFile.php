<?php
namespace ENMLibrary;

require_once("constants.php");

use Exception;
use ErrorException;
use MDBConnector\MDBDatabase;

class GradeFile {

    /*public static const COLUMNS = ["Klasse" => "Klasse", "Name" => "Name", "FachBez" => "Fach", "KursartKrz" => "Art", "KursartBez" => "Kurs", 
                                    "Kurs_ID" => "Nr.", "NotenKrz" => "Note", "Punkte" => "Pkte.", "Fehlstd" => "FS", "uFehlstd" => "uFS", "Warnung" => "M", 
                                    "Zuweisung" => "Zuw.", "FachBez" => "Fachbezogene Bemerkung", "Fx" => "Fx.", "KF" => "KF", "Komp" => "Komp.", "Schueler_ID" => "ID", 
                                    "JahrgangIntern" => "JG", "AOSF" => "AOSF", "ZieldifferentesLernen" => "ZdL", "Sortierung" => "Sort."];*/
    /*public const COLUMNS = ["Klasse" => "Klasse", "Name" => "Name", "FachBez" => "Fach", "KursartKrz" => "Art", "KurzBez" => "Kurs", "NotenKrz" => "Note", "Warnung" => "Mahnung"];*/

    /*public const COLUMNS = [["name" => "Klasse", "label" => "Klasse", "datatype" => "string", "editable" => false],
                            ["name" => "Name", "label" => "Name", "datatype" => "string", "editable" => false],
                            ["name" => "FachBez", "label" => "Fach", "datatype" => "string", "editable" => false],
                            ["name" => "KursartKrz", "label" => "Art", "datatype" => "string", "editable" => false],
                            ["name" => "KurzBez", "label" => "Kurs", "datatype" => "string", "editable" => false],
                            ["name" => "NotenKrz", "label" => "Note", "datatype" => "string", "editable" => true],
                            ["name" => "Warnung", "label" => "Mahnung", "datatype" => "string", "editable" => true, "values" => ["-" => "keine Mahnung","+" =>"Mahnung"]
                            ]];

    public const GRADE_COLUMNS = [["name" => "Krz", "label" => "Krz.", "size" => "-1"],
                            ["name" => "Bezeichnung", "label" => "Bezeichnung", "size" => "-4"],
                            ["name" => "Zeugnisnotenbez", "label" => "Zeugnisbez.", "size" => ""],
                            ["name" => "Art", "label" => "Art", "size" => " hidden"]];*/

    private $filename;
    private $db;
    private $error;

    public function __construct($filename) {
        $this->filename = $filename;
    }

    public function openFile($password=DEFAULT_DB_PASSWORD){ 
        if(!file_exists("grade-files/tmp/" . $this->filename)){
            $this->error = "File not found";
            return false;
        }

        //TODO necessary?
        set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext = null) {
            // error was suppressed with the @-operator
            if (0 === error_reporting()) {
                return false;
            }
        
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        });
        try{
            $this->db = new MDBDatabase();
            $result = $this->db->connect($this->filename, $password);
            if($result == false){
                $this->error = "Der Server ist vorübergehend nicht erreichbar. Falls es noch nicht gespeicherte Änderungen gab, wurden diese gesichert.";
                return false;
            }
            return true;
        } catch(Exception $e){
            //$this->error = $e->getMessage();
            //print_r($e->getMessage());
            return false;
        }
        
    }

    public function hasRequiredTables(){
        $requiredTables = ["SchuelerLeistungsDaten", "SchuelerLD_PSFachBem", "Kopfnoten", "SchuelerBKFaecher", "Noten", "Floskeln"];

        foreach($requiredTables as $table){
            $success = ($this->fetchTableData($table, []) !== false);
            if(!$success){
                return false;
            }
        }
        return true;
    }

    public function insertData($table, $priKeyCol, $priKey, $col, $value)
    {
        //put quotation marks around strings
        if(is_string($value)){
            $value = "'" . $value . "'";
        }

        if($value == null){
            $value = "NULL";
        }

        $sql = 'UPDATE [%s] SET [%s] = %s WHERE [%s] = %s;';
        $values = [$table, $col, $value, $priKeyCol, $priKey];
        try{
            $result = $this->db->execute($this->db->prepareStatement($sql, $values));
            print_r($result);
            return $result;
        } catch(Exception $e){
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function fetchTableData($tablename, $columns, $filter = null, $dict = false){
        $values = [];
        $columnList = "";
        $first = true;
        if(count($columns) == 0){
            $columnList = "*";
        } else {
            foreach($columns as $col){
                if($first){
                    $first = false;
                } else {
                    $columnList .= ", ";
                }
                $columnList .= "[%s]";
                $values[] = $col["name"];
            }
        }

        $sql = "SELECT " . $columnList . " FROM [%s]";
        $values[] = $tablename;
        if($filter != null){
            $sql .= " WHERE " . $filter;
        }
        $sql .= ";";

        $result = $this->db->query($this->db->prepareStatement($sql, $values), $dict);
        return $result;
    }

    public function close(){
        if($this->db != null){
            $this->db->close();
        }
    }

    public function getError(){
        return $this->error;
    }

    public function checkUser($password){
        $data = $this->fetchTableData("Users", [ [ "name" => "US_PasswordHash"]]);
        if(is_array($data)){
            foreach ($data as $user){
                if(password_verify($password, $user["US_PasswordHash"])){
                    return true;
                }
            }
        }
        return false;
    }

}

?>