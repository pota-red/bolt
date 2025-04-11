<?php

namespace Pota\Bolt;

class Stderr extends Module {

    public const int LOG_DEFAULT = 0;
    public const int LOG_DEBUG = 100;
    public const int LOG_INFO = 200;
    public const int LOG_NOTICE = 300;
    public const int LOG_WARNING = 400;
    public const int LOG_ERROR = 500;
    public const int LOG_CRITICAL = 600;
    public const int LOG_ALERT = 700;
    public const int LOG_EMERGENCY = 800;

    public function write(int $level, string $text) : void {
        $name = $_SERVER['K_SERVICE'] ?? 'UNKNOWN_SOURCE';
        $fp = fopen('php://stderr', 'wb');
        fwrite($fp, json_encode(['severity' => $level, 'message' => "$name: $text"]) . PHP_EOL);
        fclose($fp);
    }

    public function debug(string $text) : void {
        $this->write(self::LOG_DEBUG, $text);
    }

    public function info(string $text) : void {
        $this->write(self::LOG_INFO, $text);
    }

    public function warn(string $text) : void {
        $this->write(self::LOG_WARNING, $text);
    }

    public function error(string $text) : void {
        $this->write(self::LOG_ERROR, $text);
    }

}
