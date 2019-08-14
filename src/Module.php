<?php
namespace Module\MongoDriver
{
    use Poirot\Application\aSapi;
    use Poirot\Application\Interfaces\iApplication;
    use Poirot\Application\Interfaces\Sapi;
    use Poirot\Application\Interfaces\Sapi\iSapiModule;
    use Poirot\Application\Sapi\Module\ContainerForFeatureActions;

    use Poirot\Ioc\Container\BuildContainer;
    use Poirot\Loader\Autoloader\LoaderAutoloadAggregate;
    use Poirot\Loader\Autoloader\LoaderAutoloadNamespace;
    use Poirot\Loader\Interfaces\iLoaderAutoload;

    use Poirot\Std\Interfaces\Struct\iDataEntity;

    // TODO Implement Model Sample Structure to local or something default collection db
    // TODO Generator that give findAll result and change it from PersistEntity Into Domain Entity

    class Module implements iSapiModule
        , Sapi\Module\Feature\iFeatureModuleInitSapi
        , Sapi\Module\Feature\iFeatureModuleAutoload
        , Sapi\Module\Feature\iFeatureModuleMergeConfig
        , Sapi\Module\Feature\iFeatureModuleNestActions
    {
        const CONF_KEY = 'module.mongo_driver';


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
            /** @var LoaderAutoloadNamespace $nameSpaceLoader */
            $nameSpaceLoader = $baseAutoloader->loader($nameSpaceLoader);
            $nameSpaceLoader->addResource(__NAMESPACE__, __DIR__);


            require_once __DIR__.'/_functions.php';
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
        function getActions()
        {
            return \Poirot\Config\load(__DIR__ . '/../config/mod-driver_mongo.actions');
        }
    }
}


namespace Module\MongoDriver
{
    use Module\MongoDriver\Actions\MongoDriverAction;


    /**
     * @method static MongoDriverAction Driver()
     */
    class Actions extends \IOC
    { }
}
