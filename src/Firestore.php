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

    public function client() : FirestoreClient {
        return $this->client;
    }

    public function set(string $collection, string $document, array $fields) : mixed {
        $this->init();
        $collection = trim(strtolower($collection));
        return $this->client->collection($collection)->document($document)->set($fields);
    }

    public function get(string $collection, string $document) : mixed {
        $this->init();
        $collection = trim(strtolower($collection));
        return $this->client->collection($collection)->document($document);
    }

    public function collection(string $collection) : mixed {
        $this->init();
        $collection = trim(strtolower($collection));
        return $this->client->collection($collection);
    }

    public function document(string $collection, string $document) : mixed {
        $this->init();
        $collection = trim(strtolower($collection));
        return $this->client->document($document);
    }

    public function record(string $collection, string $document) : mixed {
        if ($doc = $this->document($collection, $document)) {
            if ($snap = $doc->snapshot()) {
                return $snap->data();
            }
        }
        return false;
    }

    public function recordset(string $collection) : mixed {
        $this->init();
        if ($col = $this->collection($collection)) {
            if ($docs = $col->listDocuments()) {
                $data = [];
                foreach ($docs as $doc) {
                    if ($snap = $doc->snapshot()) {
                        $data[] = $snap->data();
                    }
                }
                return $data;
            }
        }
        return false;
    }

}

