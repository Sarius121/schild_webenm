<?php
namespace ENMLibrary;

use stdClass;

class RequestResponse{

    public const ERROR_SUCCESS = 0;
    public const ERROR_MISSING_ARGUMENTS = 10;
    public const ERROR_WRONG_ARGUMENTS = 11;
    public const ERROR_CSRF_TOKEN = 21;
    public const ERROR_DOWNLOAD_TOKEN = 22;
    public const ERROR_FUNCTION_SPECIFIC = 50;

    private const ERRORS = array(
        RequestResponse::ERROR_SUCCESS => "success",
        RequestResponse::ERROR_MISSING_ARGUMENTS => "missing arguments",
        RequestResponse::ERROR_WRONG_ARGUMENTS => "wrong arguments",
        RequestResponse::ERROR_CSRF_TOKEN => "wrong CSRF token",
        RequestResponse::ERROR_DOWNLOAD_TOKEN => "wrong donwload token",
        RequestResponse::ERROR_FUNCTION_SPECIFIC => "the requested function returned an error"
    );

    private $response;

    public static function ErrorResponse($error_code, $csrf_token = null){
        return new RequestResponse(RequestResponse::ERRORS[$error_code], $error_code, $csrf_token);
    }

    public static function SuccessfulResponse($csrf_token){
        return new RequestResponse(RequestResponse::ERRORS[RequestResponse::ERROR_SUCCESS], RequestResponse::ERROR_SUCCESS, $csrf_token);
    }

    private function __construct($message, $error_code, $csrf_token)
    {
        $this->response = new stdClass();
        if(!is_null($csrf_token)){
            $this->response->csrf_token = $csrf_token;
        }
        if(!is_null($message)){
            $this->response->message = $message;
        }
        if(!is_null($error_code)){
            $this->response->code = $error_code;
        }
    }

    public function getResponse(){
        return json_encode($this->response);
    }

    public function addData($key, $value){
        $this->response->$key = $value;
    }
}

?>