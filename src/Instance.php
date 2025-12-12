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
                case 'secrets':
                    $this->secrets = new Secrets($this);
                    break;
                case 'stderr':
                    $this->stderr = new Stderr($this);
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
        if ($this->config->exists('mongodb_uri')) {
            $cfg = $this->config->get('mongodb_uri');
        } elseif ($this->config->exists('mongodb_config')) {
            $this->stderr->info("MONGO_CONFIG   " . $this->config->get('mongodb_config'));
            $cfg = (array)$this->secrets->get($this->config->get('mongodb_config'));
            $this->stderr->info("MONGO_CONFIG_DATA   " . print_r($cfg, true));
        } elseif ($this->config->exists('mongodb_host')) {
            $cfg = [
                'user' => $this->config->get('mongodb_user'),
                'pass' => $this->secrets->get($this->config->get('mongodb_secret')),
                'host' => $this->config->get('mongodb_host'),
                'port' =>$this->config->get('mongodb_port'),
                'name' => $this->config->get('mongodb_name'),
                'opts' => $this->config->get('mongodb_name')
            ];
        }
        if (is_array($cfg)) {
            if (!array_key_exists('opts', $cfg) || empty($cfg['opts'])) {
                $cfg['opts'] = "loadBalanced=true&tls=true&authMechanism=SCRAM-SHA-256&retryWrites=false";
            }
            $cfg = "mongodb://{$cfg['user']}:{$cfg['pass']}@{$cfg['host']}:{$cfg['port']}/{$cfg['name']}?{$cfg['opts']}";
        }
        if (is_string($cfg)) {
            $this->mongo = new \MongoDB\Client($cfg);
        }
    }
}
