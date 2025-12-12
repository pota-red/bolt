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

    public $mongo = null;

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
                case 'mongodb':
                    $this->mongodb();
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

    public function mongodb()  {
        $cfg = null;
        if ($this->config->exists('mongodb_config')) {
            $cfg = $this->secrets->getArray($this->config->get('mongodb_config'));
        } elseif ($this->config->exists('mongodb_host')) {
            $cfg = [
                'user' => $this->config->get('mongodb_user'),
                'pass' => $this->secrets->get($this->config->get('mongodb_secret')),
                'host' => $this->config->get('mongodb_host'),
                'port' => $this->config->get('mongodb_port'),
                'name' => $this->config->get('mongodb_name'),
                'opts' => $this->config->get('mongodb_name')
            ];
        }
        if (is_array($cfg)) {
            if (!array_key_exists('opts', $cfg) || empty($cfg['opts'])) {
                $cfg['opts'] = "loadBalanced=true&tls=true&authMechanism=SCRAM-SHA-256&retryWrites=false";
            }
            foreach ($cfg as $k => $v) {
                if ($k != 'pass') {
                    $this->config->set("mongodb_$k", $v);
                }
            }
            $cfg = "mongodb://{$cfg['user']}:{$cfg['pass']}@{$cfg['host']}:{$cfg['port']}/{$cfg['name']}?{$cfg['opts']}";
        }
        if (is_string($cfg)) {
            try {
                $this->mongo = new \MongoDB\Client($cfg);
            } catch (\Throwable $t) {
                $this->stderr->error("Unable to start MongoDB Client with specified config - " . $cfg);
                $this->stderr->error($t->getMessage());
            }
        }
    }
}
