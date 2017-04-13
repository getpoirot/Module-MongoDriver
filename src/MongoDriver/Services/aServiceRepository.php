<?php
namespace Module\MongoDriver\Services;


use Module\MongoDriver\Actions\MongoDriverAction;
use Module\MongoDriver\Model\Repository\aRepository;

use Poirot\Application\aSapi;
use Poirot\Ioc\Container\Service\aServiceContainer;
use Poirot\Std\Struct\DataEntity;

/*
$categories = $services->fresh(
    '/module/categories/services/repository/categories'
    , ['mongo_collection' => 'trades.categories'] // override options
);
$r = $categories->getTree($categories->findByID('red'));
*/

abstract class aServiceRepository
    extends aServiceContainer
{
    const CONF_REPOSITORIES = 'repositories';
    
    /** @var string Service Name */
    protected $name = 'xxxxx';


    /**
     * Create Service
     *
     * @return aRepository
     * @throws \Exception
     */
    final function newService()
    {
        $services = $this->services();

        # Prepare Options
        $mongoClient      = $this->optsData()->getMongoClient();
        $mongoCollection  = $this->optsData()->getMongoCollection();
        $mongoPersistable = $this->optsData()->getMongoPersistable();

        $mongoClient      = ($mongoClient)      ? $mongoClient      : $this->_getConf('collection', 'client');
        $mongoPersistable = ($mongoPersistable) ? $mongoPersistable : $this->_getConf('persistable');
        $mongoCollection  = ($mongoCollection)  ? $mongoCollection  : $this->_getConf('collection', 'name');
        if (!$mongoCollection)
            throw new \Exception('Collection name not available from Config or neither Options.');
        if (!$mongoClient)
            throw new \Exception(sprintf(
                'Client name for collection (%s) not available from Config or neither Options.'
                , $mongoCollection
            ));

        /** @var MongoDriverAction $mongoDriver */
        $mongoDriver     = \Module\MongoDriver\Actions\IOC::Driver();
        $db              = $mongoDriver->database(MongoDriverAction::SELECT_DB_FROM_CONFIG, $mongoClient);

        return $this->newRepoInstance($db, $mongoCollection, $mongoPersistable);
    }

    /**
     * Return new instance of Repository
     *
     * @param \MongoDB\Database  $mongoDb
     * @param string             $collection
     * @param string|object|null $persistable
     *
     * @return aRepository
     */
    abstract function newRepoInstance($mongoDb, $collection, $persistable = null);


    // ..

    /**
     * // TODO config as array access
     * Get Config Values
     *
     * Argument can passed and map to config if exists [$key][$_][$__] ..
     * @param $key
     * @param null $_
     *
     * @return mixed|null
     * @throws \Exception
     */
    protected function _getConf($key = null, $_ = null)
    {
        // retrieve and cache config
        $services = $this->services();

        /** @var aSapi $config */
        $config   = $services->get('/sapi');
        $orig = $config   = $config->config();
        /** @var DataEntity $config */
        $config   = $config->get(\Module\MongoDriver\Module::CONF_KEY, array());

        if (!isset($config[self::CONF_REPOSITORIES]) && !is_array($config[self::CONF_REPOSITORIES]))
            throw new \Exception('Mongo Driver Module, Repositories Config Not Available.');


        $config   = $config[self::CONF_REPOSITORIES];
        if (! isset($config[$this->_getRepoKey()])) {
            throw new \Exception(sprintf(
                'Mongo Driver Module, No Config Available for repository (%s).'
                , $this->_getRepoKey()
            ));
        }



        # Retrieve requested config key(s)
        $config   = $config[$this->_getRepoKey()];
        $keyconfs = func_get_args();
        foreach ($keyconfs as $key) {
            if (!isset($config[$key]))
                return null;

            $config = $config[$key];
        }

        return $config;
    }

    /**
     * Get Key Of Merged Config To Retrieve Settings
     *  \Module\Categories\Module::CONF_KEY
     *
     * @return string
     */
    final function _getRepoKey()
    {
        return static::class;
    }
}
