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

        // unbatched (sync) logging - logs will appear as they are emitted
        // benefit:
        //      logging entries will appear as they are sent, before script return
        // side-effects:
        //      increased RPC latency for each logging call
        $this->client = $lc->psrLogger($this->source);

        // batched (async) logging - logs will appear when the script returns
        // benefit:
        //      decreased RPC latency during script execution
        // side-effects:
        //      all emitted logs will only appear after script execution
        //$this->client = LoggingClient::psrBatchLogger($this->source);
    }

    public function __call(string $loglevel, array $arguments) : void {
        try {
            $this->client->$loglevel($this->source . ": " . $arguments[0]);
        } catch (\Throwable $e) {
            $this->instance->stderr->error("bolt::logger " . $e->getMessage());
        }
    }
}
