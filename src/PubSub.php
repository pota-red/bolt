<?php

namespace Pota\Bolt;

use Google\Cloud\PubSub\MessageBuilder;
use Google\Cloud\PubSub\PubSubClient;

class PubSub {

    private PubSubClient|null $client = null;
    private Bolt $app;

    public function __construct(Bolt $app) {
        $this->app = $app;
    }

    private function init() : void {
        if (is_null($this->client)) {
            $this->client = new PubsubClient;
        }
    }

    private function topicName(string $name) : string {
        return implode($this->app->config->get('pubsub/separator'), [
            $this->app->config->get('env/branch'),
            trim(strtolower($name))
        ]);
    }

    public function send(string $topicName, array|object $data) : bool {
        $this->init();
        $topic = $this->client->topic($this->topicName($topicName));
        return (bool)count($topic->publish((new MessageBuilder())->setData(json_encode($data))->build()));
    }

}