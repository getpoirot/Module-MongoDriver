<?php
namespace Module\MongoDriver
{
    use Module\MongoDriver\Events\ModulesPostLoad\RegisterMongoRepositoriesListener;
    use Module\MongoDriver\Sapi\Feature\iFeatureMongoRepositories;
    use Poirot\Application\Interfaces\Sapi;
    use Poirot\Application\Interfaces\Sapi\iSapiModule;
    use Poirot\Application\ModuleManager\EventHeapOfModuleManager;
    use Poirot\Application\ModuleManager\Interfaces\iModuleManager;
    use Poirot\Application\Sapi\ModuleManager;
    use Poirot\Ioc\Container;
    use Poirot\Loader\Autoloader\LoaderAutoloadAggregate;
    use Poirot\Std\Interfaces\Struct\iDataEntity;


    class Module implements iSapiModule
        , Sapi\Module\Feature\iFeatureModuleInitSapi
        , Sapi\Module\Feature\iFeatureModuleAutoload
        , Sapi\Module\Feature\iFeatureModuleInitModuleManager
        , Sapi\Module\Feature\iFeatureModuleMergeConfig
        , Sapi\Module\Feature\iFeatureModuleNestServices
        , Sapi\Module\Feature\iFeatureModuleNestActions
        , iFeatureMongoRepositories
    {
        /**
         * @inheritdoc
         */
        function initialize($sapi)
        {
            if (! extension_loaded('mongodb') )
                throw new \RuntimeException('Mongodb driver extension not installed.');
        }

        /**
         * @inheritdoc
         */
        function initAutoload(LoaderAutoloadAggregate $baseAutoloader)
        {
            $nameSpaceLoader = \Poirot\Loader\Autoloader\LoaderAutoloadNamespace::class;
            /** @var \Poirot\Loader\Autoloader\LoaderAutoloadNamespace $nameSpaceLoader */
            $nameSpaceLoader = $baseAutoloader->loader($nameSpaceLoader);
            $nameSpaceLoader->addResource(__NAMESPACE__, __DIR__);

            require_once __DIR__.'/_functions.php';
        }

        /**
         * @inheritdoc
         */
        function initModuleManager(iModuleManager $moduleManager)
        {
            ## Add Ability To Get Collection Repositories From Modules
            #
            /** @var ModuleManager $moduleManager */
            $moduleManager->event()->on(
                EventHeapOfModuleManager::EVENT_MODULES_POSTLOAD
                , new RegisterMongoRepositoriesListener
                , -2000
            );
        }

        /**
         * @inheritdoc
         */
        function initConfig(iDataEntity $config)
        {
            return \Poirot\Config\load(__DIR__ . '/../config/mod-driver_mongo');
        }

        /**
         * @inheritdoc
         */
        function getServices(Container $moduleContainer = null)
        {
            return include __DIR__ . '/../config/services.conf.php';
        }

        /**
         * @inheritdoc
         */
        function getActions()
        {
            return include_once __DIR__ . '/../config/actions.conf.php';
        }

        /**
         * @inheritDoc
         */
        function registerMongoRepositories()
        {
            return include_once __DIR__ . '/../config/repositories.conf.php';
        }
    }
}
