<?php

namespace Pota\Bolt;

abstract class Stderr {

    protected function logWrite(string $level, string $function, string $text) : void {
        $name = $_SERVER['K_SERVICE'] ?? 'UNKNOWN_SOURCE';
        $fp = fopen('php://stderr', 'wb');
        fwrite($fp, json_encode(['severity' => $level, 'message' => "$name: $function: $text"]) . PHP_EOL);
        fclose($fp);
    }

}
