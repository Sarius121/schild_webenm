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
                            ["name" => "Zeugnisnotenbez", "label" => "Zeugnisbez.", "size" => ""],
                            ["name" => "Art", "label" => "Art", "size" => " hidden"]];

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
        /*$options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES => false
        ];*/
        set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext = null) {
            // error was suppressed with the @-operator
            if (0 === error_reporting()) {
                return false;
            }
        
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        });
        try{
            //$this->db = new PDO("odbc:DRIVER={Microsoft Access Driver (*.mdb, *.accdb)};charset=UTF-8;DBQ=" . $this->filename . ";PWD=" . $password . ";");
            //$this->db = odbc_connect("DRIVER={Microsoft Access Driver (*.mdb, *.accdb)};charset=UTF-8; DBQ=" . $this->filename . ";", "", $password);
            $this->db = new MDBDatabase();
            $this->db->connect($this->filename, $password);
        } catch(Exception $e){
            $this->error = $e->getMessage();
            print_r($e->getMessage());
            return false;
        }

        return true;
        //print_r($this->db->errorInfo());
        
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
        $value = utf8_decode($value);
        //put quotation marks around strings
        if(is_string($value)){
            $value = "'" . $value . "'";
        }

        $sql = 'UPDATE [' . $table . '] SET [' . $col . '] = ' . $value . ' WHERE [' . $priKeyCol . '] = ' . $priKey . ';';
        print_r($sql);
        try{
            $result = $this->db->execute($sql);
            print_r($result);
            //$result = $this->db->query($sql)->fetchAll();
            //$result = odbc_exec($this->db, $sql);
        } catch(Exception $e){
            $this->error = $e->getMessage();
            return false;
        }
        //TODO error handling
    }

    public function fetchTableData($tablename, $columns, $filter = null, $dict = false){
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
                $columnList .= "[" . $col["name"] . "]";
            }
        }

        $sql = "SELECT " . $columnList . " FROM [" . $tablename . "]";
        if($filter != null){
            $sql .= " WHERE " . $filter;
        }
        $sql .= ";";

        //$result = odbc_exec($this->db, $sql);
        //$result = $this->db->query($sql)->fetchAll();
        $result = $this->db->query($sql, $dict);
        //while($dbRow = odbc_fetch_array($result)){
        /*foreach ($result as $dbRow) {
            $row = [];
            
            for($i = $startCol; $i < count($columns); $i++){
                //$row[array_values(GradeFile::COLUMNS)[$i]] = $dbRow[array_keys(GradeFile::COLUMNS)[$i]];
                //print_r($dbRow);
                $row[$columns[$i]["name"]] = utf8_encode($dbRow[$columns[$i]["name"]]);
            }

            if($dict){
                $table[utf8_encode($dbRow[$columns[0]["name"]])] = $row;
            } else {
                $table[] = $row;
            }
            
        }*/
        return $result;
    }

    /**
     * helper function for debugging, will be deleted at the end
     * 
     */
    /*public function getTables(){
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

    /**
     * helper function for debugging, will be deleted at the end
     * @deprecated
     */
    /*public function getRawTableData($tablename){
        $sql = "select * from " . $tablename . ";";
        $result = $this->db->query($sql);
        return $result;
    }*/

    public function close(){
        //$this->db = null;
        $this->db->close();
        //odbc_close($this->db);
    }

    public function getError(){
        return $this->error;
    }

}

?>