<?php
/**
 *
 * @see \Poirot\Ioc\Container\BuildContainer
 */
use Poirot\Ioc\Container\BuildContainer;


return array(
    'services' => array(
        'driver' => \Module\MongoDriver\Actions\MongoDriverService::class,
    ),
);
