<?php

namespace MDBConnector;

use Exception;
use mysqli;

require_once("constants.php");

/**
 * This class establishes a connection to a server which handles all accesses to the database files and sends sql statements to this server to be executed.
 */
class MDBDatabase{


    private $connID;

    public function __construct($connID = null){
        if($connID != null){
            $this->connID = $connID;
        }
    }

    public function connect($file, $password){
        $data = [ "file" => $file, "password" => $password, "debug" => "false" ]; //you may want to set debug = true
        $result = $this->doRequest("mdbconnect", $data);
        if($result != false){
            $jsonResult = json_decode($result, true);
            if($jsonResult["success"]){
                $this->connID = $jsonResult["connID"];
                return $this->connID;
            }
        }
        return false;
    }

    public function execute($sql){
        if(!$this->isConnected()){
            return false;
        }
        $data = [ "connID" => $this->connID, "method" => "execute", "sql" => $sql ];
        $result = $this->doRequest("mdbexecute", $data);
        if($result != false){
            $jsonResult = json_decode($result, true);
            return $jsonResult["success"];
        }
        return false;
    }

    public function query($sql, $dict = false){
        if(!$this->isConnected()){
            return false;
        }
        $dictStr = "false";
        if($dict){
            $dictStr = "true";
        }
        $data = [ "connID" => $this->connID, "method" => "query", "sql" => $sql, "dict" => $dictStr ];
        $result = $this->doRequest("mdbexecute", $data);
        if($result != false){
            $jsonResult = json_decode($result, true);
            if($jsonResult["success"]){
                return $jsonResult["data"];
            }
        }
        return false;
    }

    protected function doRequest($path, $data){
        $url = "http://" . MDB_CONN_SERVER . ":" . MDB_CONN_PORT . "/" . MDB_CONN_PATH . $path;

        $data["apisecret"] = API_SECRET;

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            )
        );
        $context  = stream_context_create($options);

        try{
            $result = file_get_contents($url, false, $context);
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }

    public function close(){
        if(!$this->isConnected()){
            return false;
        }
        $result = $this->doRequest("mdbclose", [ "connID" => $this->connID ]);
        if($result != false){
            $jsonResult = json_decode($result, true);
            if($jsonResult["success"]){
                $this->connID = null;
                return true;
            }
        }
        return false;
    }

    public function closeWithoutConnection($file){
        $result = $this->doRequest("mdbclose", [ "file" => $file ]);
        if($result != false){
            $jsonResult = json_decode($result, true);
            if($jsonResult["success"]){
                return true;
            }
        }
        return false;
    }

    public function isConnected(){
        return $this->connID != null;
    }

    public function prepareStatement($sql, $values){
        for($i = 0; $i < count($values); $i++){
            $values[$i] = $this->escape($values[$i]);
        }
        return sprintf($sql, $values);
    }

    /**
     * edited function from https://stackoverflow.com/questions/4892882/mysql-real-escape-string-for-multibyte-without-a-connection
     */
    public function escape($string) {
        $return = '';
        for($i = 0; $i < strlen($string); $i++) {
            $char = $string[$i];
            $ord = ord($char);
            if($char !== "'" && $char !== "\"" && $char !== '\\' && $ord >= 32 && $ord <= 126)
                $return .= $char;
            else
                $return .= '\\x' . dechex($ord);
        }
        return $return;
    }

}

?>