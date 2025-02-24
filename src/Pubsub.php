<?php

namespace Pota\Bolt;

use Google\Cloud\PubSub\MessageBuilder;
use Google\Cloud\PubSub\PubSubClient;

class PubSub {

    private PubSubClient|null $client = null;
    private Config $config;

    public function __construct(Config $config) {
        $this->config = $config;
    }

    private function init() : void {
        if (is_null($this->client)) {
            $this->client = new PubsubClient;
        }
    }

    protected function send(string $topic, array $data) : bool {
        $this->init();
        $topic = $this->client->topic($topic);
        return (bool)count($topic->publish((new MessageBuilder())->setData(json_encode($data))->build()));
    }

}