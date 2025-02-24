# POTA Bolt

Base loader / interface framework for GCP Cloud Run instances.

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

#read local config
$cfg = parse_ini_file(__DIR__ . '/.env');

$app = new Bolt;
$app->config()->setArray($cfg);

# storage module
$app->storage();

# pubsub module
$app->pubsub();

# firestore module
$app->firestore();

```