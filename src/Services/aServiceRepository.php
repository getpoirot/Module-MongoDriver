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

    protected $mongoClient;
    protected $mongoCollection;
    protected $mongoPersistable;
    protected $dbName;


    /**
     * Create Service
     *
     * @return aRepository
     * @throws \Exception
     */
    final function newService()
    {
        # Prepare Options
        $mongoClient      = $this->mongoClient;
        $mongoCollection  = $this->mongoCollection;
        $mongoPersistable = $this->mongoPersistable;
        $mongoDatabase    = $this->dbName;

        $mongoClient      = ($mongoClient)      ? $mongoClient      : $this->_getConf('self', 'collection', 'client');
        $mongoCollection  = ($mongoCollection)  ? $mongoCollection  : $this->_getConf('self', 'collection', 'name');
        $mongoDatabase    = ($mongoDatabase)    ? $mongoDatabase    : $this->_getConf('self', 'collection', 'db_name');
        $mongoPersistable = ($mongoPersistable) ? $mongoPersistable : $this->_getConf('self', 'persistable');


        if (! $mongoDatabase )
            // Try to get default database name
            $mongoDatabase = $this->_getConf(self::class, 'db_name');

        if (! $mongoCollection )
            throw new \Exception('Collection name not available from Config or neither Options.');

        if (! $mongoClient )
            // Try to get default client
            $mongoClient = $this->_getConf(self::class, 'client');


        /** @var MongoDriverAction $mongoDriver */
        $mongoDriver = \Module\MongoDriver\Actions::Driver();
        $db          = $mongoDriver->getClient($mongoClient)
            ->selectDatabase($mongoDatabase);

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
     *
     * @param null $repo
     * @param $key
     * @param null $_
     *
     * @return mixed|null
     * @throws \Exception
     */
    protected function _getConf($repo = null, $key = null, $_ = null)
    {
        // retrieve and cache config
        $services = $this->services();
        ($repo !== 'self') ?: $repo = $this->_getRepoKey();

        /** @var aSapi $config */
        $config = $services->get('/sapi');
        $config = $config->config();
        /** @var DataEntity $config */
        $config = $config->get(\Module\MongoDriver\Module::CONF_KEY, array());

        if (! isset($config[self::CONF_REPOSITORIES]) || !is_array($config[self::CONF_REPOSITORIES]) )
            return null;


        $config   = $config[self::CONF_REPOSITORIES];
        if (! isset($config[$repo]) )
            // Config not found for this repository
            return null;

        $config   = $config[$repo];



        ## Retrieve requested config key(s)
        #
        $keyconfs = func_get_args();
        array_shift($keyconfs);

        foreach ($keyconfs as $key) {
            if (! isset($config[$key]) )
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


    // Options

    /**
     * @param mixed $mongoClient
     */
    function setMongoClient($mongoClient)
    {
        $this->mongoClient = $mongoClient;
    }

    /**
     * @param mixed $mongoCollection
     */
    function setMongoCollection($mongoCollection)
    {
        $this->mongoCollection = $mongoCollection;
    }

    /**
     * @param mixed $mongoPersistable
     */
    function setMongoPersistable($mongoPersistable)
    {
        $this->mongoPersistable = $mongoPersistable;
    }

    /**
     * @param mixed $dbName
     */
    function setDbName($dbName)
    {
        $this->dbName = $dbName;
    }
}