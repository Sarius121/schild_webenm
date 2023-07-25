<?php
namespace ENMLibrary;

use Exception;
use stdClass;

class RequestResponse{

    public const ERROR_SUCCESS = 0;
    public const ERROR_UNKNOWN = 1;
    public const ERROR_MISSING_ARGUMENTS = 10;
    public const ERROR_WRONG_ARGUMENTS = 11;
    public const ERROR_CSRF_TOKEN = 21;
    public const ERROR_DOWNLOAD_TOKEN = 22;
    public const ERROR_FUNCTION_SPECIFIC = 50;

    private const ERRORS = array(
        RequestResponse::ERROR_SUCCESS => "success",
        RequestResponse::ERROR_UNKNOWN => "unknown",
        RequestResponse::ERROR_MISSING_ARGUMENTS => "missing arguments",
        RequestResponse::ERROR_WRONG_ARGUMENTS => "wrong arguments",
        RequestResponse::ERROR_CSRF_TOKEN => "wrong CSRF token",
        RequestResponse::ERROR_DOWNLOAD_TOKEN => "wrong download token",
        RequestResponse::ERROR_FUNCTION_SPECIFIC => "the requested function returned an error"
    );

    private $response;

    /**
     * create an error response
     * 
     * @param string $error_code one of the error code constants in this class
     * @param ?string $csrf_token new csrf-token to send back
     * @param ?Exception $exception exception which has occurred and whose message should be sent as 'details'
     *                  (it's only sent if DEBUG_MESSAGES is true)
     */
    public static function ErrorResponse(string $error_code, string $csrf_token = null, ?Exception $exception = null, ?string $errorId = null): RequestResponse{
        $response = new RequestResponse(RequestResponse::ERRORS[$error_code], $error_code, $csrf_token);
        if (!is_null($exception) && DEBUG_MESSAGES) {
            $response->addDetailedMessage($exception->getMessage());
        }
        if (!is_null($errorId)) {
            $response->addData("errorid", $errorId);
        }
        return $response;
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

    /**
     * add a detailed message (key = 'details')
     * 
     * @param string $message detailed message as value for the key 'details'
     */
    public function addDetailedMessage(string $message): void {
        $this->addData("details", $message);
    }
}

?>