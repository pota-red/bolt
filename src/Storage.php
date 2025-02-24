<?php

namespace Pota\Bolt;

use Google\Cloud\Storage\StorageClient;

class Storage {

    private StorageClient|null $client = null;
    private Bolt $app;

    public function __construct(Bolt $app) {
        $this->app = $app;
    }

    private function init() : void {
        if (is_null($this->client)) {
            $this->client = new StorageClient;
        }
    }

    public function bucketName(string $name) : string {
        return implode($this->app->config->get('storage/separator'), [
            $this->app->config->get('env/project'),
            $this->app->config->get('env/branch'),
            trim(strtolower($name))
        ]);
    }

    public function move(string $fromBucket, string $fromObjectName, string $toBucket, string $toObjectName = null) : void {
        $this->init();
        $bucket = $this->client->bucket($this->bucketName($fromBucket));
        $object = $bucket->object($fromObjectName);
        $object->copy($toBucket, ['name' => empty($toObjectName) ? $fromObjectName : $toObjectName]);
        $object->delete();
        $this->app->stderr->write(LOG_INFO, "Moved $fromBucket:$fromObjectName to $toBucket");
    }

    public function upload(string $bucketName, string $objectName, string $data) : void {
        $this->init();
        $bucket = $this->client->bucket($this->bucketName($bucketName));
        $bucket->upload($data, ['name' => $objectName]);
        $this->app->stderr->write(LOG_INFO, "Upload $bucketName:$objectName");
    }

    public function download(string $bucketName, string $objectName, string $destination) : void {
        $this->init();
        $bucket = $this->client->bucket($this->bucketName($bucketName));
        $object = $bucket->object($objectName);
        $object->downloadToFile($destination);
        $this->app->stderr->write(LOG_INFO, "Download $bucketName:$objectName to $destination");
    }

    public function get(string $bucketName, string $objectName) : ?string {
        $this->init();
        $bucket = $this->client->bucket($this->bucketName($bucketName));
        $object = $bucket->object($objectName);
        return $object->downloadAsString();
    }

}
