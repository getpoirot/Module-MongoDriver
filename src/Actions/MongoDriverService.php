<?php
namespace Module\MongoDriver\Actions;

use Module\MongoDriver\Module;
use Poirot\Ioc\Container\Service\aServiceContainer;
use Poirot\Std\Struct\DataEntity;


class MongoDriverService
    extends aServiceContainer
{
    /**
     * @var string Service Name
     */
    protected $name = 'driver';


    /**
     * Create Service
     *
     * @return mixed
     */
    function newService()
    {
        # build with merged config
        /** @var DataEntity $config */
        $services = $this->services();
        $config = $services->get('/sapi')->config();
        $config = $config->get(Module::CONF_KEY, array());

        $mongoDriver = new MongoDriverAction($config);
        return $mongoDriver;
    }
}
