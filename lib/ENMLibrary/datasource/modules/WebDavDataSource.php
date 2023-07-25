<?php

namespace ENMLibrary\datasource\modules;

use ENMLibrary\datasource\DataSourceModule;
use ENMLibrary\LoggingHandler;
use Sabre\DAV\Client;
use Sabre\HTTP\Request;

include 'vendor/autoload.php';

class WebDavDataSource extends DataSourceModule {

    public static function getName(): string {
        return "WebDav";
    }

    private ?Client $client;

    public function __construct() {
        parent::__construct();

        $settings = array(
            'baseUri' => WEBDAV_URL,
            'userName' => WEBDAV_USER,
            'password' => WEBDAV_PWD
        );
        
        if (defined("WEBDAV_PROXY")) {
            $settings['proxy'] = WEBDAV_PROXY;
        }

        $this->client = new Client($settings);

        // TODO this is just a work around because otherwise the method has to be distinguished first and the PUT will fail
        $this->client->addCurlSetting(CURLOPT_HTTPAUTH, Client::AUTH_BASIC);

        $response = $this->client->options();

        if (array_search(1, $response) === false || array_search(3, $response) === false) {
            LoggingHandler::getLogger()->error("WebDAV server connection not possible", [LoggingHandler::LOCATION => "WebDavDataSource.__construct()"]);
            $this->client = null;
        }
    }

    public function openFile(): bool {
        if ($this->client == null) {
            return false;
        }
        
        $request = new Request("GET", $this->client->getAbsoluteUrl($this->getSourceFile()));
        $response = $this->client->send($request);

        if ($response->getStatus() != 200) {
            LoggingHandler::getLogger()->error("WebDAV server responded with " . $response->getStatus(), [LoggingHandler::LOCATION => "WebDavDataSource.openFile()"]);
            return false;
        }

        $file = fopen($this->getTargetFile(), "w");
        stream_copy_to_stream($response->getBodyAsStream(), $file);
        fclose($file);
        
        return true;
    }

    public function saveFile(): bool {
        if ($this->client == null) {
            return false;
        }

        $file = fopen($this->getTargetFile(), "r");
        $response = $this->client->send(new Request('PUT', $this->client->getAbsoluteUrl($this->getSourceFile()), [], $file));
        fclose($file);

        if ($response->getStatus() < 400) {
            return true;
        }
        LoggingHandler::getLogger()->error("WebDAV server responded with " . $response->getStatus(), [LoggingHandler::LOCATION => "WebDavDataSource.saveFile()"]);
        return false;
    }

    public function findFilenameImpl(string $username): ?string {
        if ($this->client == null) {
            return null;
        }
        
        $response = $this->client->propfind('', array('{DAV:}givenname'), 1);
        
        foreach ($response as $file => $info) {
            $filename = urldecode(basename(($file)));
            if (preg_match("/^" . preg_quote($username) . preg_quote(FILE_SUFFIX) . ".*\.enz$/", $filename) == 1) {
                return basename($filename, '.enz');
            }
        }

        return null;
    }

    public function getFilesInfos(): array {
        if ($this->client == null) {
            return (array) null;
        }

        $response = $this->client->propfind('', array(
            '{DAV:}getlastmodified'
        ), 1);
        
        $result = array();
        foreach ($response as $file => $info) {
            if (pathinfo($file, PATHINFO_EXTENSION) !== "enz") {
                continue;
            }
            $fileInfos = array();
            $fileInfos["file"] = urldecode(basename($file, ".enz"));
            $fileInfos["user"] = $this->getUsernameByFilename($fileInfos["file"]);
            $fileInfos["last-edit"] = strtotime($info["{DAV:}getlastmodified"]);
            $result[] = $fileInfos;
        }

        return $result;
    }

    public function getModuleInformation(): array
    {
        $status = "Verbindung nicht möglich";
        if ($this->client != null) {
            $status = "Verbunden";
        }
        $infos = ["WebDAV-Status:" => $status];
        return $infos;
    }
}

?>