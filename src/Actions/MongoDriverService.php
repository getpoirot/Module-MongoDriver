<?php
namespace Module\MongoDriver\Actions;

use Module\MongoDriver\Module;
use Poirot\Ioc\Container\Service\aServiceContainer;


class MongoDriverService
    extends aServiceContainer
{
    const CONF = 'clients';


    /**
     * @inheritdoc
     * @return MongoDriverAction
     */
    function newService()
    {
        $config = \Poirot\config(Module::class, self::CONF);
        if ( empty($config) )
            throw new \InvalidArgumentException('MongoDriver Configuration For Clients Not Found.');


        $mongoDriver = new MongoDriverAction($config);
        return $mongoDriver;
    }
}
