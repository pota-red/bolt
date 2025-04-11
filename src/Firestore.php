<?php

namespace Pota\Bolt;

use Google\Cloud\Firestore\FirestoreClient;

class Firestore extends Module {

    private FirestoreClient|null $client;

    public function _initialize() : void {
        $this->client = new FirestoreClient([
            'project' => $this->instance->config->get('project'),
            'database' => $this->instance->config->get('firestore.database')
        ]);
    }

    public function client() : FirestoreClient {
        return $this->client;
    }

    public function collection(string $collection) : \Google\Cloud\Firestore\CollectionReference
    {
        $collection = trim(strtolower($collection));
        return $this->client->collection($collection);
    }

    public function document(string $collection, string $document) : \Google\Cloud\Firestore\DocumentReference
    {
        $collection = trim(strtolower($collection));
        return $this->client->document("{$collection}/{$document}");
    }

}
