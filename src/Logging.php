<?php

namespace Pota\Bolt;

use Google\Cloud\Logging\LoggingClient;

class Logging extends Module {
    private LoggingClient|null $client = null;

    protected function _initialize() : void {
        $this->client = new LoggingClient();
    }

    public function log() : LoggingClient {
        return $this->client;
    }

}
