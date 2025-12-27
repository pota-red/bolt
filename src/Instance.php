<?php

namespace Pota\Bolt;

date_default_timezone_set('UTC');

class Instance {

    public Config|null $config = null;
    public Firestore|null $firestore = null;
    public Secrets|null $secrets = null;
    public Stderr|null $stderr = null;
    public PubSub|null $pubsub = null;
    public Storage|null $storage = null;

    public function __construct(array $services = []) {
        $this->stderr = new Stderr($this);
        $this->secrets = new Secrets($this);
        $this->config = new Config;
        $this->config->set('separator', '--');
        foreach (getenv() as $k => $v) {
            if (str_starts_with(strtoupper($k), 'BOLT_')) {
                $this->config->set(strtolower(substr($k, 5)), $v);
            }
        }
        foreach ($services as $service) {
            switch (trim(strtolower($service))) {
                case 'storage':
                    $this->storage = new Storage($this);
                    break;
                case 'pubsub':
                    $this->pubsub = new PubSub($this);
                    break;
                case 'firestore':
                    $this->firestore = new Firestore($this);
                    break;
            }
        }
    }

    public function debug() : \stdClass {
        $data = new \stdClass;
        $data->config = $this->config->get();
        $data->services = [
            'storage' => get_class($this->storage),
            'pubsub' => get_class($this->pubsub),
            'secrets' => get_class($this->secrets),
            'stderr' => get_class($this->stderr),
            'firestore' => get_class($this->firestore)
        ];
        return $data;
    }

    public function tick(float $value = 0) : float|int {
        $x = hrtime(true);
        return ($value != 0) ? round(($x - $value) / 1e+6, 3) : $x;
    }

}
