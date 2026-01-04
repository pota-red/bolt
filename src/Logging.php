<?php

namespace Pota\Bolt;

use Google\Cloud\Logging\LoggingClient;
use Google\Cloud\Logging\PsrLogger;

class Logging extends Module {
    private PsrLogger|null $client = null;

    protected function _initialize() : void {
        $lc = new LoggingClient();
        $this->client = $lc->psrLogger('app');

    }

    public function log() : PsrLogger {
        return $this->client;
    }
}
