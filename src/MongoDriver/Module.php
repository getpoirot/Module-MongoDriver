<?php
namespace Module\MongoDriver;

use Poirot\Application\aSapi;
use Poirot\Application\Interfaces\iApplication;
use Poirot\Application\Sapi;
use Poirot\Application\Interfaces\Sapi\iSapiModule;
use Poirot\Application\SapiCli;
use Poirot\Application\SapiHttp;

use Poirot\Ioc\Container;

use Poirot\Loader\Autoloader\LoaderAutoloadAggregate;
use Poirot\Loader\Autoloader\LoaderAutoloadNamespace;
use Poirot\Loader\Interfaces\iLoaderAutoload;

use Poirot\Std\Interfaces\Struct\iDataEntity;

// TODO Implement Model Sample Structure to local or something default collection db

class Module implements iSapiModule
    , Sapi\Module\Feature\FeatureModuleInitSapi
    , Sapi\Module\Feature\FeatureModuleAutoload
    , Sapi\Module\Feature\FeatureModuleMergeConfig
    , Sapi\Module\Feature\FeatureModuleNestFacade
    , Sapi\Module\Feature\FeatureOnPostLoadModulesGrabServices
{
    const CONF_KEY = 'module.mongo-driver';

    /**
     * Init Module Against Application
     *
     * - determine sapi server, cli or http
     *
     * priority: 1000 A
     *
     * @param iApplication|aSapi $sapi Application Instance
     *
     * @return void
     */
    function initialize($sapi)
    {
        if (!extension_loaded('mongodb'))
            throw new \RuntimeException('Mongodb driver extension not installed.');
        
        
    }

    /**
     * Register class autoload on Autoload
     *
     * priority: 1000 B
     *
     * @param LoaderAutoloadAggregate $baseAutoloader
     *
     * @return iLoaderAutoload|array|\Traversable|void
     */
    function initAutoload(LoaderAutoloadAggregate $baseAutoloader)
    {
        #$nameSpaceLoader = \Poirot\Loader\Autoloader\LoaderAutoloadNamespace::class;
        $nameSpaceLoader = 'Poirot\Loader\Autoloader\LoaderAutoloadNamespace';
        /** @var LoaderAutoloadNamespace $nameSpaceLoader */
        $nameSpaceLoader = $baseAutoloader->by($nameSpaceLoader);
        $nameSpaceLoader->addResource(__NAMESPACE__, __DIR__);
    }
    
    /**
     * Register config key/value
     *
     * priority: 1000 D
     *
     * - you may return an array or Traversable
     *   that would be merge with config current data
     *
     * @param iDataEntity $config
     *
     * @return array|\Traversable
     */
    function initConfig(iDataEntity $config)
    {
        return include __DIR__.'/../../config/module.conf.php';
    }

    /**
     * Get Module As Service Instance
     *
     * priority not that serious
     *
     * @param Container $nestedModulesContainer
     *
     * @return mixed
     */
    function getModuleAsFacade(Container $nestedModulesContainer = null)
    {
        return new MongoDriverManagementFacade;
    }

    /**
     * Resolve to service with name
     *
     * - each argument represent requested service by registered name
     *   if service not available default argument value remains
     * - "services" as argument will retrieve services container itself.
     *
     * ! after all modules loaded
     *
     * @param Container $services service names must have default value
     * @param aSapi|SapiHttp|SapiCli $sapi
     *
     * @throws \Exception
     */
    function resolveRegisteredServices($services = null, $sapi = null)
    {
        ## Build Mongo Client Managements With Merged Configs
        
        $config = $sapi->config()->get(self::CONF_KEY);

        if ($config) {
            /** @var MongoDriverManagementFacade $mongoDriver */
            $mongoDriver = $services->get('/module/mongodriver');
            $mongoDriver->with($config);
        }
        
    }
}
