<?php
namespace Module\MongoDriver\Services;

use Module\MongoDriver\MongoDriverManagementFacade;
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
    const CONF_KEY = 'mongo-driver-repos';
    
    /** @var string Service Name */
    protected $name = 'xxxxx';
    
    protected $default_collection = 'xxxxx';

    /**
     * Create Service
     *
     * @return mixed
     */
    final function newService()
    {
        $services = $this->services();

        $this->__prepareOptions();

        /** @var MongoDriverManagementFacade $mongoDriver */
        $mongoDriver     = $services->get('/module/mongoDriver');
        $db              = $mongoDriver->database($this->optsData()->getMongoClient());
        $modelRepository = $this->getRepoClassName();
        $modelRepository = new $modelRepository($db, $this->getCollectionName());

        return $modelRepository;
    }

    /**
     * Get Key Of Merged Config To Retrieve Settings
     *  \Module\Categories\Module::CONF_KEY
     *
     * @return string
     */
    abstract function getMergedConfKey();

    /**
     * Repository Class Name
     *   Module\Categories\Model\Repository\Categories
     *   
     * @return string Instanceof Module\MongoDriver\Model\Repository\aRepository
     */
    abstract function getRepoClassName();

    /**
     * Get Collection Name
     *
     * @return string
     */
    function getCollectionName()
    {
        $collection = $this->optsData()->getMongoCollection();
        
        return ($collection) ? $collection : $this->default_collection;
    }

    /**
     * Retrieve and merge options from application merged config
     * @throws \Exception
     */
    protected function __prepareOptions()
    {
        $services    = $this->services();

        /** @var aSapi $config */
        $config       = $services->get('/sapi');
        $config       = $config->config();
        
        /** @var DataEntity $config */
        $config = $config->get(\Module\MongoDriver\Module::CONF_KEY, array());
        if (! isset($config[$this->getMergedConfKey()]))
            // Nothing to do; Config unavailable!!
            return;
        else 
            $config = $config[$this->getMergedConfKey()];

        $mongoCollection = $this->getCollectionName();
        if (!$mongoCollection)
            throw new \Exception('DB Collection name for categories not defined.');

        if (!$this->optsData()->getMongoClient()) {
            $mongoClient = (isset($config['collections'][$mongoCollection]['client']))
                ? $config['collections'][$mongoCollection]['client']
                : MongoDriverManagementFacade::CLIENT_DEFAULT;

            $this->optsData()->setMongoClient($mongoClient);
        }
    }
}
