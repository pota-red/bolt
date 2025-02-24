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

```php
<?php

require_once 'vendor/autoload.php';

use Pota\Bolt\Bolt;
use Psr\Http\Message\ServerRequestInterface;

class app extends Bolt {

    public function init() {
        # setup configuration
        $this->config->set('env/project', 'pota-red');
        $this->config->set('env/branch', getenv('BRANCH_NAME'));
        $this->config->set('firestore/database/name', getenv('BRANCH_NAME') == 'prod' ? '(default)' : 'devel');
        $this->config->loadIni(__DIR__ . '/.env');
    }

    public function process() {
        # logic here
    }

}

function run(ServerRequestInterface $request) {
    new app($request);
}


```