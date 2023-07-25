<?php
namespace ENMLibrary;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class LoggingHandler {

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

}

?>