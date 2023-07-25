<?php
namespace ENMLibrary;

use Exception;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class LoggingHandler {

    public const LOCATION = "location";

    private static ?Logger $logger = null;

    public static function initLogger(?string $user, ?string $fileid, array $extras = []) {
        LoggingHandler::$logger = new Logger("webENM");
        LoggingHandler::$logger->pushHandler(new StreamHandler(LOG_FILE, LOG_LEVEL));
        LoggingHandler::$logger->pushProcessor(function ($record) use ($user, $fileid, $extras) {
            $record["extra"]["user"] = $user;
            $record["extra"]["fileid"] = $fileid;
            $record["extra"] = array_merge($record["extra"], $extras);
            return $record;
        });
    }

    private static function initDefaultLogger() {
        LoggingHandler::$logger = new Logger("webENM");
        LoggingHandler::$logger->pushHandler(new StreamHandler(LOG_FILE, LOG_LEVEL));
        LoggingHandler::$logger->pushProcessor(function ($record) {
            $record->extra["logger"] = "default";
        });
    }

    public static function getLogger() {
        if (is_null(LoggingHandler::$logger)) {
            LoggingHandler::initDefaultLogger();
        }
        return LoggingHandler::$logger;
    }

    public static function logTrackableError($message, array $context = []) {
        $errorId = uniqid("error");
        $context["errorid"] = $errorId;
        LoggingHandler::getLogger()->error($message, $context);
        return $errorId;
    }

    public static function logTrackableException(Exception $e, array $context = []) : string {
        $errorId = uniqid("error");
        $context["errorid"] = $errorId;
        $context[LoggingHandler::LOCATION] = $e->getFile() . ":" . $e->getLine();
        LoggingHandler::getLogger()->error($e->getMessage(), $context);
        return $errorId;
    }

}

?>