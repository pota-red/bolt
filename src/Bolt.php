<?php

namespace Pota\Bolt;

date_default_timezone_set('UTC');

use Google\CloudFunctions\FunctionsFramework;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class Bolt {

    public const string STATUS_OK = '200';

    public Config|null $config = null;
    public Stderr|null $stderr = null;
    public Router|null $router = null;
    public PubSub|null $pubsub = null;
    public Storage|null $storage = null;
    public Firestore|null $firestore = null;

    public function __construct() {
        $this->stderr = new Stderr;
        $this->config = new Config;
        $this->config->set('storage/separator', '--');
        $this->config->set('pubsub/separator', '--');
        $this->router = new Router($this);
        $this->storage = new Storage($this);
        $this->pubsub = new PubSub($this);
        $this->firestore = new Firestore($this);
        $this->stderr->write(LOG_INFO, "Initialize " . get_class($this));
        if (is_callable([$this, 'init'])) {
            $this->init();
        }
    }

    public function run() : void {
        FunctionsFramework::http(get_class($this), [$this, 'handle']);
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface {
        try {
            $uriPath = $request->getUri()->getPath() ?? null;
            $httpMethod = $request->getMethod();
            $data = $this->router->handle($httpMethod, $uriPath, ['body' => $request->getBody()]);
            $out = new Response(200, ['Content-type' => 'application/json'], json_encode(['data' => $data]));
        } catch (\Throwable $e) {
            $this->stderr->write(LOG_ERR, $e->getMessage());
            $out = new Response(500, ['Content-type' => 'application/json'], json_encode(['error' => $e->getMessage()]));
        }
        return $out;
    }

}
