<?php

namespace Pota\Bolt;

use Google\Cloud\Storage\StorageClient;

class Storage extends Stderr {

    private StorageClient|null $client = null;
    private Config $config;

    public function __construct(Config $config) {
        $this->config = $config;
    }

    private function init() : void {
        if (is_null($this->client)) {
            $this->client = new StorageClient;
        }
    }

    public function bucketName(string $name) : string {
        return implode($this->config->get('storage/separator'), [
            $this->config->get('storage/bucket/prefix'),
            $this->config->get('env/branch'),
            trim(strtolower($name))
        ]);
    }

    public function move(string $fromBucket, string $fromObjectName, string $toBucket, string $toObjectName = null) : void {
        $this->init();
        $bucket = $this->client->bucket($fromBucket);
        $object = $bucket->object($fromObjectName);
        $object->copy($toBucket, ['name' => empty($toObjectName) ? $fromObjectName : $toObjectName]);
        $object->delete();
        $this->logWrite(LOG_INFO, __FUNCTION__, "Moved $fromBucket:$fromObjectName to $toBucket");
    }

    public function upload(string $bucketName, string $objectName, string $data) : void {
        $this->init();
        $bucket = $this->client->bucket($bucketName);
        $bucket->upload($data, ['name' => $objectName]);
        $this->logWrite(LOG_INFO, __FUNCTION__, "Upload $bucketName:$objectName");
    }

    public function download(string $bucketName, string $objectName, string $destination) : void {
        $this->init();
        $bucket = $this->client->bucket($bucketName);
        $object = $bucket->object($objectName);
        $object->downloadToFile($destination);
        $this->logWrite(LOG_INFO, __FUNCTION__, "Download $bucketName:$objectName to $destination");
    }

    public function get(string $bucketName, string $objectName) : ?string {
        $this->init();
        $bucket = $this->client->bucket($bucketName);
        $object = $bucket->object($objectName);
        return $object->downloadAsString();
    }

}
