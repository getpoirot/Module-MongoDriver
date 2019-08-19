<?php
namespace Module\MongoDriver\Services;

use Module\MongoDriver\Actions\MongoDriverAction;
use Module\MongoDriver\Model\Repository\aRepository;
use Module\MongoDriver\Services;
use Poirot\Ioc\Container\Service\aServiceContainer;

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
    protected $mongoClient;
    protected $dbName;
    protected $mongoCollection;
    protected $mongoPersistable;


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


    /**
     * Create Service
     *
     * @return aRepository
     * @throws \Exception
     */
    final function newService()
    {
        ## Prepare Options
        #
        $mongoClient      = $this->mongoClient ?? $this->_getConf(static::class, 'collection', 'client');
        $mongoCollection  = $this->mongoCollection ?? $this->_getConf(static::class, 'collection', 'name');
        $mongoDatabase    = $this->mongoPersistable ?? $this->_getConf(static::class, 'collection', 'db_name');
        $mongoPersistable = $this->dbName ?? $this->_getConf(static::class, 'persistable');

        if (! $mongoDatabase )
            // Try to get default database name
            $mongoDatabase = $this->_getConf(aServiceRepository::class, 'db_name');

        if (! $mongoCollection )
            throw new \Exception('Collection name not available from Config or neither Options.');

        if (! $mongoClient )
            // Try to get default client
            $mongoClient = $this->_getConf(aServiceRepository::class, 'client');


        ## Create Repository Instance
        #
        /** @var MongoDriverAction $mongoDriver */
        $mongoDriver = \Module\MongoDriver\Actions::Driver();
        $db = $mongoDriver->client($mongoClient)
            ->selectDatabase($mongoDatabase);

        return $this->newRepoInstance($db, $mongoCollection, $mongoPersistable);
    }

    // Options:

    /**
     * Set Mongo Client Name
     *
     * @param string $mongoClient
     */
    function setMongoClient($mongoClient)
    {
        $this->mongoClient = $mongoClient;
    }

    /**
     * Set Database Name
     *
     * @param string $dbName
     */
    function setDbName($dbName)
    {
        $this->dbName = $dbName;
    }

    /**
     * Mongo Collection Associated To This Repo
     *
     * @param string $mongoCollection
     */
    function setMongoCollection($mongoCollection)
    {
        $this->mongoCollection = $mongoCollection;
    }

    /**
     * Set Entity Persistable Object Map
     *
     * @param mixed $mongoPersistable
     */
    function setMongoPersistable($mongoPersistable)
    {
        $this->mongoPersistable = $mongoPersistable;
    }

    // ..

    /**
     * Get Config Values
     *
     * Argument can passed and map to config if exists [$key][$_][$__] ..
     *
     * @param string $repo
     * @param $key
     * @param null $_
     *
     * @return mixed|null
     * @throws \Exception
     */
    protected function _getConf($repo, $key = null, $_ = null)
    {
        /** @var ReposRegistry $reposRegistry */
        $reposRegistry = $this->services()->from('/module/mongodriver/services')
            ->get(Services::ReposRegistry);


        ## Retrieve requested config key(s)
        #
        $reposRegistry = $reposRegistry->get($repo, []);

        $arguments = func_get_args();
        array_shift($arguments);

        foreach ($arguments as $key) {
            if (! isset($reposRegistry[$key]) )
                return null;

            $reposRegistry = $reposRegistry[$key];
        }

        return $reposRegistry;
    }
}
