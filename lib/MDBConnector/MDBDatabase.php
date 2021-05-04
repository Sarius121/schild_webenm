<?php

namespace MDBConnector;

/**
 * This class establishes a connection to a server which handles all accesses to the database files and sends sql statements to this server to be executed.
 */
class MDBDatabase{

    private const MDB_FILE_SERVER = "localhost";
    private const MDB_FILE_PORT = 8080;

    private $connID;

    public function __construct($connID = null){
        if($connID != null){
            $this->connID = $connID;
        }
    }

    public function connect($file, $password){
        $data = [ "file" => $file, "password" => $password, "debug" => "true" ];
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
        $url = "http://" . MDBDatabase::MDB_FILE_SERVER . ":" . MDBDatabase::MDB_FILE_PORT . "/" . $path;

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            )
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        return $result;
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

}

?>