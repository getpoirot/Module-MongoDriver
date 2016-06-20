<?php
if (!file_exists(__DIR__.'/vendor/autoload.php')) {
    throw new \RuntimeException( "Unable to load Module.\n"
        . "- Type `composer install`; this module require 3rd party libraries.\n"
    );
}

## autoload composer
require_once __DIR__.'/vendor/autoload.php';

## load module
require_once __DIR__ . '/src/MongoDriver/Module.php';
