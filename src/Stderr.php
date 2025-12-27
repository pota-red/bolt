<?php

namespace Pota\Bolt;

class Stderr extends Module {

    public const string LOG_DEFAULT = 'DEFAULT';
    public const string LOG_DEBUG = 'DEBUG';
    public const string LOG_INFO = 'INFO';
    public const string LOG_NOTICE = 'NOTICE';
    public const string LOG_WARNING = 'WARNING';
    public const string LOG_ERROR = 'ERROR';
    public const string LOG_CRITICAL = 'CRITICAL';
    public const string LOG_ALERT = 'ALERT';
    public const string LOG_EMERGENCY = 'EMERGENCY';

    public function write(string $level, string $text) : void {
        $name = $_SERVER['K_SERVICE'] ?? 'UNKNOWN_SOURCE';
        $fp = fopen('php://stderr', 'wb');
        fwrite($fp, json_encode(['severity' => $level, 'message' => "$name: $text"]) . PHP_EOL);
        fclose($fp);
    }

    public function default(string $text) : void {
        $this->write(self::LOG_DEFAULT, $text);
    }

    public function debug(string $text) : void {
        $this->write(self::LOG_DEBUG, $text);
    }

    public function info(string $text) : void {
        $this->write(self::LOG_INFO, $text);
    }

    public function notice(string $text) : void {
        $this->write(self::LOG_NOTICE, $text);
    }

    public function warn(string $text) : void {
        $this->write(self::LOG_WARNING, $text);
    }

    public function error(string $text) : void {
        $this->write(self::LOG_ERROR, $text);
    }
    public function critical(string $text) : void {
        $this->write(self::LOG_CRITICAL, $text);
    }
    public function alert(string $text) : void {
        $this->write(self::LOG_ALERT, $text);
    }
    public function emergency(string $text) : void {
        $this->write(self::LOG_EMERGENCY, $text);
    }
}
