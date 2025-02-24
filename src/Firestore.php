<?php

namespace Pota\Bolt;

use Google\Cloud\Firestore\FirestoreClient;
use Throwable;

class Firestore extends Stderr {

    private FirestoreClient|null $client = null;
    private Config $config;

    public function __construct(Config $config) {
        $this->config = $config;
    }

    public function init() : void {
        if (is_null($this->client)) {
            try {
                $this->client = new FirestoreClient([
                    'project' => $this->config->get('env/project'),
                    'database' => $this->config->get('firestore/database')
                ]);
            } catch (Throwable $e) {
                $this->logWrite(LOG_ERR, __FUNCTION__, $e->getMessage());
            }
        }
    }

}

