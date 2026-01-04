<?php

namespace Pota\Bolt;

use Google\Cloud\Logging\LoggingClient;
use Google\Cloud\Logging\PsrLogger;
use Stringable;
use Throwable;

class Logging extends Module {
    private PsrLogger|null $client = null;
    private string|null $source = null;

    protected function _initialize() : void {
        $lc = new LoggingClient();

        // unbatched (sync) logging - logs will appear as they are emitted
        // benefit:
        //      logging entries will appear as they are sent, before script return
        // side-effects:
        //      increased RPC latency for each logging call
        $this->client = $lc->psrLogger($_SERVER['K_SERVICE'] ?? 'UNKNOWN_SOURCE');

        // batched (async) logging - logs will appear when the script returns
        // benefit:
        //      decreased RPC latency during script execution
        // side-effects:
        //      all emitted logs will only appear after script execution
        //
        //$this->client = $lc->psrLogger($this->source, ['batchEnabled' => true]);
    }

    public function emergency(string $label_name, string $label_value, string $text) : void {
        $this->client->emergency($this->makeText($text), $this->makeLabel($label_name, $label_value));
    }

    public function alert(string $label_name, string $label_value, string $text) : void {
        $this->client->alert($this->makeText($text), $this->makeLabel($label_name, $label_value));
    }

    public function critical(string $label_name, string $label_value, string $text) : void {
        $this->client->critical($this->makeText($text), $this->makeLabel($label_name, $label_value));
    }

    public function error(string $label_name, string $label_value, string $text) : void {
        $this->client->error($this->makeText($text), $this->makeLabel($label_name, $label_value));
    }

    public function warning(string $label_name, string $label_value, string $text) : void {
        $this->client->warning($this->makeText($text), $this->makeLabel($label_name, $label_value));
    }

    public function notice(string $label_name, string $label_value, string $text) : void {
        $this->client->notice($this->makeText($text), $this->makeLabel($label_name, $label_value));
    }

    public function info(string $label_name, string $label_value, string $text) : void {
        $this->client->info($this->makeText($text), $this->makeLabel($label_name, $label_value));
    }

    public function debug(string $label_name, string $label_value, string $text) : void {
        $this->client->debug($this->makeText($text), $this->makeLabel($label_name, $label_value));
    }

    private function makeLabel(string $name, string $value) :  array {
        return ['stackdriverOptions' => ['labels' => [$name => $value]]];
    }

    private function makeText(string $value) : string {
        $k_service = $_SERVER['K_SERVICE'] ?? null;
        return $k_service ? "$k_service: $value" : "UNKNOWN_SOURCE: $value";
    }
}
