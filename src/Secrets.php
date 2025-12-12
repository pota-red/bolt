<?php

namespace Pota\Bolt;

use Google\Cloud\SecretManager\V1\Client\SecretManagerServiceClient;
use Google\Cloud\SecretManager\V1\AccessSecretVersionRequest;

class Secrets extends Module {

    private SecretManagerServiceClient|null $client = null;

    public function _initialize() : void {
        $this->client = new SecretManagerServiceClient();
    }

    private function secret(string $name, string $version = 'latest') : mixed {
        $name = implode($this->instance->config->get('separator'), [$this->instance->config->get('branch'), trim(strtolower($name))]);
        return $this->client->secretVersionName($this->instance->config->get('project'), $name, $version);
    }

    public function get(string $name, string $version = 'latest') : string {
        $secret = $this->secret($name, $version);
        try {
            $req = $this->client->accessSecretVersion(AccessSecretVersionRequest::build($secret));
            if ($data = $req->getPayload()->getData()) {
                return $data;
            }
        } catch (\Throwable $t) {
            $this->instance->stderr->error("bolt::secrets::get  " . $t->getMessage());
        }
        return '';
    }

    public function getJson(string $name, string $version = 'latest') : object|null {
        $sec = $this->get($name, $version);
        $this->instance->stderr->info("bolt::secrets::getJSON  " . $sec);
        return json_decode($sec);
    }

    public function getArray(string $name, string $version = 'latest') : array|null {
        $sec = $this->get($name, $version);
        $this->instance->stderr->info("bolt::secrets::getArray  " . $sec);
        return json_decode($sec, true);
    }

}