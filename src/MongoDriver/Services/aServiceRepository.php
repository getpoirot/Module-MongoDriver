<?php
namespace Module\MongoDriver\Services;

use Module\MongoDriver\MongoDriverManagementFacade;
use Poirot\Application\aSapi;
use Poirot\Ioc\Container\Service\aServiceContainer;
use Poirot\Std\Struct\DataEntity;

/*
$categories = $services->fresh(
    '/module/categories/services/repository/categories'
    , ['db_collection' => 'trades.categories'] // override options
);
$r = $categories->getTree($categories->findByID('red'));
*/

abstract class aServiceRepository
    extends aServiceContainer
{
    const CONF_KEY = 'mongo_driver';
    
    /** @var string Service Name */
    protected $name = 'xxxxx';

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
        $db              = $mongoDriver->query($this->optsData()->getMongoClient());
        $modelRepository = $this->getRepoClassName();
        $modelRepository = new $modelRepository($db, $this->optsData()->getDbCollection());

        return $modelRepository;
    }

    /**
     * Repository Class Name
     *   Module\Categories\Model\Repository\Categories
     *
     * @return string
     */
    abstract function getRepoClassName();

    /**
     * Get Key Of Merged Config To Retrieve Settings
     *  \Module\Categories\Module::CONF_KEY
     *
     * @return string
     */
    abstract function getMergedConfKey();


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
        $config = $config->get($this->getMergedConfKey(), array());
        if (! isset($config[self::CONF_KEY])) 
            // Nothing to do; Config unavailable!!
            return;
        else 
            $config = $config[self::CONF_KEY];
        
        if (!$this->optsData()->getMongoClient()) {
            $mongoClient = (isset($config['client']))
                ? $config['client']
                : MongoDriverManagementFacade::CLIENT_DEFAULT;

            $this->optsData()->setMongoClient($mongoClient);
        }

        if (!$this->optsData()->getDbCollection()) {
            $mongoCollection = (isset($config['collection']))
                ? $config['collection']['name']
                : null;

            if (!$mongoCollection)
                throw new \Exception('DB Collection name for categories not defined.');

            $this->optsData()->setDbCollection($mongoCollection);
        }
    }
}
