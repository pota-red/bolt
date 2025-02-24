# Bolt

Base loader / interface framework for GCP Cloud Run invokables.

## Usage

### Setup Composer

```composer
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/pota-red/bolt"
        }
    ],
    "require": {
        "pota/bolt": "@dev"
    }
}
```

`composer update`

### Basic Script

The class name (eg: `helloworld`) is the GCP Cloud Run name.

```php
<?php

require_once 'vendor/autoload.php';

use Pota\Bolt\Bolt;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class helloworld extends Bolt {

    public function init() {
        # setup configuration
        $this->config->set('env/project', 'pota-red');
        $this->config->set('env/branch', getenv('BRANCH_NAME'));
        $this->config->set('firestore/database/name', getenv('BRANCH_NAME') == 'prod' ? '(default)' : 'devel');
        $this->config->loadIni(__DIR__ . '/.env');
        # register endpoints
        $this->router->get('/health', 'health');
        $this->router->get('/v1/hello', 'getHello');
        $this->router->post('/v1/hello', 'postHello');
    }

    public function health() : mixed {
        return Bolt::STATUS_OK;
    }

    public function getHello() : mixed {
        return 'Hello Earthlings!';
    }
    
    public function postHello(array $input) : mixed {
        return "Hello {$input[0]}";
    }

}

(new helloworld)->run();


```