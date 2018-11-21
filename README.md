Yii2 Configloader
=================

[![Build Status](https://secure.travis-ci.org/codemix/yii2-configloader.png)](http://travis-ci.org/codemix/yii2-configloader)
[![Latest Stable Version](https://poser.pugx.org/codemix/yii2-configloader/v/stable.svg)](https://packagist.org/packages/codemix/yii2-configloader)
[![Latest Unstable Version](https://poser.pugx.org/codemix/yii2-configloader/v/unstable.svg)](https://packagist.org/packages/codemix/yii2-configloader)
[![Total Downloads](https://poser.pugx.org/codemix/yii2-configloader/downloads)](https://packagist.org/packages/codemix/yii2-configloader)
[![License](https://poser.pugx.org/codemix/yii2-configloader/license.svg)](https://packagist.org/packages/codemix/yii2-configloader)

Build configuration arrays from config files and environment variables.

## Features

You can use this extension to solve some or all of the following tasks:

 * Build Yii2 configuration arrays for web and console application
 * Initialize Yii environment (`YII_DEBUG`, `YII_ENV`) from environment variables
 * Load environment variables from a `.env` file
 * Get config options from environment variables
 * Load local configuration overrides
 * Streamline config initialization and Yii 2 bootstrapping

## Installation

Install the package with [composer](http://getcomposer.org):

    composer require codemix/yii2-configloader


## Description

We mainly use this extension to configure our dockerized yii2 applications.
It's [good practice](https://12factor.net/) to build your docker applications in such a way,
that the runtime configuration in productive mode happens solely via environment variables.

But during local development we can loosen these strict requirement a little as we
sometimes have to add debug options or the like that should not be part of the main
configuration. Here the extension helps to override settings with local configuration
files that live outside of version control.

You have several options how to use this extension:

 1. Use only the Yii environment initialization
 2. Use only the configuration loader
 3. Use both

We first show how to use the first two options "standalone" and then a third,
combined way that includes all features.


### 1. Initializing Yii environment

This will set the `YII_DEBUG` and `YII_ENV` variables according to the respective
environment variables if those are set. It can also load them from a `.env` file.

In debug mode `error_reporting()` will also be set to `E_ALL`.

```php
<?php
use codemix\yii2confload\Config;

Config::initEnv('/path/to/app');
$setting = Config::env('MY_SETTING', 'default');
```

If you leave away the application path, no `.env` file will be loaded.


### 2. Loading configuration

If want to load your configuration with this extenstion, the following naming scheme
must be followed:

 * `config/web.php` - Web configuration
 * `config/console.php` - Console configuration
 * `config/local.php` - Local overrides for the web configuration (optional)
 * `config/local-console.php` - Local overrides for the console configuration (optional)

If you only want to load configuration from files but whithout initializing the Yii
environments as shown above, you'd create a `Config` instance and pass the application
base directory and, as second argument, `false` to the constructor:

```php
<?php
use codemix\yii2confload\Config;
$config = new Config('/path/to/app', false);
// Reads configuration from config/web.php
$webConfig = $config->web();
```

#### 2.1 Local configuration

By default local configuration files `local.php` and `local-console.php` are not
loaded. To activate this feature you can either set the `ENABLE_LOCALCONF` environment
variable (either in your server environment or in `.env`):

```
ENABLE_LOCALCONF=1
```

Now the methods will return the corresponding merged results:

 * `web()`: `config/web.php` + `config/local.php`
 * `console()`: `config/console.php` + `config/local-console.php`

Alternatively you can explicitely ask for local configuration:

```php
<?php
use codemix\yii2confload\Config;
$config = new Config('/path/to/app', false);
// Merges configuration from config/web.php and config/local.php if present
$webConfig = $config->web([], true);
// Merges configuration from config/console.php and config/local-console.php if present
$consoleConfig = $config->console([], true);
```

#### 2.2 Merging custom configuration

You can also inject some other configuration when you fetch the web or console config:

```php
<?php
use codemix\yii2confload\Config;
$config = new Config('/path/to/app', false);
$webConfig = $config->web(['id' => 'test'], true);
```


### 3. Initialize Yii environment and load configuration

Let's finally show a full example that demonstrates how to use all the mentioned
features in one go. A typical setup will use the following files:

#### `.env`

Here we define the Yii environment and DB credentials. You'd add more config options
in the same manner:

```
YII_DEBUG=1
YII_ENV=dev

DB_DSN=mysql:host=db.example.com;dbname=web
DB_USER=user
DB_PASSWORD='**secret**'
```

#### `config/web.php`

This file is later included in the scope of `codemix\yii2confload\Config`, so you
can easily access instance and class methods:

```php
<?php
/* @var codemix\yii2confload\Config $this */
return [
    'components' => [
        'db' => [
            'dsn' => self::env('DB_DSN', 'mysql:host=db;dbname=web'),
            'username' => self::env('DB_USER', 'web'),
            'password' => self::env('DB_PASSWORD', 'web'),
        ],
```

#### `config/console.php`

Having access to the config instance allows for example to reuse parts of your web
configuration in your console config.

```php
<?php
/* @var codemix\yii2confload\Config $this */

$web = $this->web();
return [
    // ...
    'components' => [
        'db' => $web['components']['db'],
```

#### `web/index.php`

We've streamlined the process of setting up a `Config` object and loading the
Yii 2 bootstrap file into a single method `Config::boostrap()` which only
receives the application directory as argument.

```php
<?php
use codemix\yii2confload\Config;

require(__DIR__ . '/../vendor/autoload.php');
$config = Config::bootstrap(__DIR__ . '/..');
Yii::createObject('yii\web\Application', [$config->web()])->run();
```

This makes sure that things are loaded in the right order. If you prefer a more
verbose version, the code above is equivalent to:

```php
<?php
use codemix\yii2confload\Config;

require(__DIR__ . '/../vendor/autoload.php');
$config = new Config(__DIR__ . '/..');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

Yii::createObject('yii\web\Application', [$config->web()])->run();
```

#### `yii`

The same approach is used for the console application:

```php
<?php
use codemix\yii2confload\Config;

require(__DIR__ . '/vendor/autoload.php');
$config = Config::bootstrap(__DIR__);
$application = Yii::createObject('yii\console\Application', [$config->console()]);
exit($application->run());
```
