<?php

namespace Pota\Bolt;

use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\MessageBuilder;

class PubSub extends Module {

    private PubSubClient|null $client = null;

    protected function _initialize() : void {
        $this->client = new PubSubClient();
    }

    private function topicName(string $name) : string {
        return implode($this->instance->config->get('separator'), [$this->instance->config->get('branch'), trim(strtolower($name))]);
    }

    public function send(string $topicName, array|object $data) : bool {
        $topic = $this->client->topic($this->topicName($topicName));
        return (bool)count($topic->publish((new MessageBuilder())->setData(json_encode($data))->build()));
    }

    public static function decodeData(object $data) : object {
        return isset($data->message->data) ? json_decode(base64_decode($data->message->data)) : (object)[];
    }

}