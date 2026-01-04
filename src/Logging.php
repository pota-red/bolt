<?php

namespace Pota\Bolt;

use Google\Cloud\Logging\LoggingClient;
use Google\Cloud\Logging\PsrLogger;
use Throwable;

class Logging extends Module {
    private PsrLogger|null $client = null;
    private string|null $source = null;

    protected function _initialize() : void {
        $lc = new LoggingClient();
        $this->source = $_SERVER['K_SERVICE'] ?? 'UNKNOWN_SOURCE';

        // unbatched logging - logs will appear as they are emitted
        $this->client = $lc->psrLogger($this->source);

        // batched logging - logs will appear when the script returns
        //$this->client = LoggingClient::psrBatchLogger($this->source);
    }

    public function __call(string $name, array $arguments) : void {
        try {
            $this->client->$name($this->source . ": " . $arguments[0]);
        } catch (\Throwable $e) {
            $this->instance->stderr->error("bolt::logger " . $e->getMessage());
        }
    }
}
