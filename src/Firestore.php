<?php

namespace Pota\Bolt;

use Google\Cloud\Firestore\FirestoreClient;
use Throwable;

class Firestore {

    private FirestoreClient|null $client = null;
    private Bolt $app;

    public function __construct(Bolt $app) {
        $this->app = $app;
    }

    public function init() : void {
        if (is_null($this->client)) {
            try {
                $this->client = new FirestoreClient([
                    'project' => $this->app->config->get('env/project'),
                    'database' => $this->app->config->get('firestore/database/name')
                ]);
            } catch (Throwable $e) {
                $this->app->stderr->write(LOG_ERR, __FUNCTION__, $e->getMessage());
            }
        }
    }

}

