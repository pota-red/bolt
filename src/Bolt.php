<?php

namespace Pota\Bolt;

date_default_timezone_set('UTC');

use Google\CloudFunctions\FunctionsFramework;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class Bolt {

    public ServerRequestInterface|null $request = null;
    public object|null $input = null;
    public string|null $uri = null;
    public array|null $query = null;
    public string|null $method = null;
    public Config|null $config = null;
    public Stderr|null $stderr = null;
    public PubSub|null $pubsub = null;
    public Storage|null $storage = null;
    public Firestore|null $firestore = null;

    public function __construct(ServerRequestInterface $request) {
        $this->request = $request;
        $params = $request->getServerParams();
        $type = $params['CONTENT_TYPE'] ?? null;
        $input = trim(file_get_contents('php://input'));
        switch ($type) {
            case 'application/json':
            case 'application/x-www-form-urlencoded':
                $this->input = !empty($input) ? json_decode($input) : null;
                break;
        }
        $this->uri = $request->getUri()->getPath() ?? null;
        $this->query = $request->getQueryParams() ?? null;
        $this->method = $request->getMethod() ?? null;
        $this->stderr = new Stderr;
        $this->config = new Config;
        $this->config->set('storage/separator', '--');
        $this->config->set('pubsub/separator', '--');
        $this->storage = new Storage($this);
        $this->pubsub = new PubSub($this);
        $this->firestore = new Firestore($this);
        $this->stderr->write(LOG_INFO, "Initialize " . get_class($this));
        if (is_callable([$this, 'init'])) {
            $this->init();
        }
        if  (is_callable([$this, 'process'])) {
            $this->process();
        }
    }

    public function output(int $status, string|array|null $data = null, array $headers = []) : Response {
        $data = is_array($data) ? json_encode($data) : null;
        return new Response($status, $headers, $data);
    }

}
