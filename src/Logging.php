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

    public function emergency(string $text) : void {
        $this->client->emergency($this->source . ": " . $text);
    }

    public function alert(string $text) : void {
        $this->client->alert($this->source . ": " . $text);
    }

    public function critical(string $text) : void {
        $this->client->critical($this->source . ": " . $text);
    }

    public function error(string $text) : void {
        $this->client->error($this->source . ": " . $text);
    }

    public function warning(string $text) : void {
        $this->client->warning($this->source . ": " . $text);
    }
    public function notice(string $text) : void {
        $this->client->notice($this->source . ": " . $text);
    }

    public function info(string $text) : void {
        $this->client->info($this->source . ": " . $text);
    }

    public function debug(string $text) : void {
        $this->client->debug($this->source . ": " . $text);
    }
}
