<?php

namespace Pota\Bolt;

class Module {

    protected Instance $instance;

    public function __construct(Instance $instance) {
        $this->instance = $instance;
        if (is_callable([$this, '_initialize'])) {
            $this->_initialize();
        }
    }
}