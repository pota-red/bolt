<?php

namespace Pota\Bolt;

class Stderr {

    public function write(int $level, string $text) : void {
        $name = $_SERVER['K_SERVICE'] ?? 'UNKNOWN_SOURCE';
        $fp = fopen('php://stderr', 'wb');
        fwrite($fp, json_encode(['severity' => $level, 'message' => "$name: $text"]) . PHP_EOL);
        fclose($fp);
    }

}
