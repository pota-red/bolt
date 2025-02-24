<?php

namespace Pota\Bolt;

class Bolt {

    private Config|null $config = null;
    private PubSub|null $pubsub = null;
    private Storage|null $storage = null;
    private Firestore|null $firestore = null;

    public function __construct() {
        $this->config = new Config;
        $this->config->set('env/project', 'pota-red');
        $this->config->set('env/branch', getenv('BRANCH_NAME'));
        $this->config->set('storage/separator', '--');
        $this->config->set('storage/bucket/prefix', $this->config->get('env/project'));
        $this->config->set('firestore/database/name', getenv('BRANCH_NAME') == 'prod' ? '(default)' : 'devel');
        $this->storage = new Storage($this->config);
        $this->pubsub = new PubSub($this->config);
        $this->firestore = new Firestore($this->config);
    }

    public function config() : Config {
        return $this->config;
    }

    public function pubsub() : PubSub {
        return $this->pubsub;
    }

    public function storage() : Storage {
        return $this->storage;
    }

    public function firestore() : Firestore {
        return $this->firestore;
    }

}
