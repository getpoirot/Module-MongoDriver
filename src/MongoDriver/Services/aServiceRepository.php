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
        $db              = $mongoDriver->database($this->optsData()->getClientName());
        $modelRepository = $this->getRepoClassName();
        $modelRepository = new $modelRepository($db, $this->optsData()->getMongoCollection());

        return $modelRepository;
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

    /**
     * Repository Class Name
     *   Module\Categories\Model\Repository\Categories
     *   
     * @return string Instanceof Module\MongoDriver\Model\Repository\aRepository
     */
    abstract function getRepoClassName();
    
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
        if (! isset($config['repositories'][$this->_getRepoKey()]))
            // Nothing to do; Config unavailable!!
            return;
        else 
            $config = $config['repositories'][$this->_getRepoKey()];

        if (!$this->optsData()->getMongoCollection()) {
            $mongoCollection = (isset($config['collection']['name']))
                ? $config['collection']['name']
                : MongoDriverManagementFacade::CLIENT_DEFAULT;

            $this->optsData()->setMongoCollection($mongoCollection);
        }
        
        if (!$this->optsData()->getMongoClient()) {
            $mongoClient = (isset($config['collection']['client']))
                ? $config['collection']['client']
                : MongoDriverManagementFacade::CLIENT_DEFAULT;

            $this->optsData()->setMongoClient($mongoClient);
        }
    }
}
