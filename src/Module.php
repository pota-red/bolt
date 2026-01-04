<?php

namespace Pota\Bolt;

use Google\Cloud\Logging\LoggingClient;

class Module {

    protected Instance $instance;

    public function __construct(Instance $instance) {
        $this->instance = $instance;
        if (is_callable([$this, '_initialize'])) {
            $this->_initialize();
        }
    }
}
