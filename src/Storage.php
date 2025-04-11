<?php

namespace Pota\Bolt;

use Google\Cloud\Storage\StorageClient;

class Storage extends Module {

    private StorageClient|null $client = null;

    public function _initialize() : void {
        $this->client = new StorageClient;
    }

    public function bucketName(string $name) : string {
        return implode($this->instance->config->get('separator'), [
            $this->instance->config->get('project'),
            $this->instance->config->get('branch'),
            trim(strtolower($name))
        ]);
    }

    public function move(string $fromBucket, string $fromObjectName, string $toBucket, string $toObjectName = null) : void {
        $bucket = $this->client->bucket($this->bucketName($fromBucket));
        $object = $bucket->object($fromObjectName);
        $object->copy($toBucket, ['name' => empty($toObjectName) ? $fromObjectName : $toObjectName]);
        $object->delete();
    }

    public function upload(string $bucketName, string $objectName, string $data) : bool {
        $bucket = $this->client->bucket($this->bucketName($bucketName));
        $bucket->upload($data, ['name' => $objectName]);
        return true;
    }

    public function download(string $bucketName, string $objectName, string $destination) : void {
        $bucket = $this->client->bucket($this->bucketName($bucketName));
        $object = $bucket->object($objectName);
        $object->downloadToFile($destination);
    }

    public function get(string $bucketName, string $objectName) : ?string {
        $bucket = $this->client->bucket($this->bucketName($bucketName));
        $object = $bucket->object($objectName);
        return $object->downloadAsString();
    }

}
