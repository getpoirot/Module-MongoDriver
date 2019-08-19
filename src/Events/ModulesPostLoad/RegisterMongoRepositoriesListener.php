<?php
namespace Module\MongoDriver\Events\ModulesPostLoad;

use Module\MongoDriver\Sapi\Feature\iFeatureMongoRepositories;
use Module\MongoDriver\Services;
use Module\MongoDriver\Services\ReposRegistry;
use Poirot\Application\aSapi;
use Poirot\Application\Sapi\ModuleManager;
use function Poirot\Std\flatten;


class RegisterMongoRepositoriesListener
{
    /** @var ModuleManager */
    protected $moduleManager;
    /** @var ReposRegistry */
    protected $_reposRegistry;


    /**
     * Set Repositories Provided By Modules Into Registry
     *
     * @param ModuleManager $module_manager
     * @throws \Exception
     */
    function __invoke($module_manager = null)
    {
        $this->moduleManager = $module_manager;
        foreach($module_manager->listLoadedModules() as $moduleName)
        {
            $module = $module_manager->byModule($moduleName);
            if (! $module instanceof iFeatureMongoRepositories )
                // Nothing to do!
                continue;


            $repositories = $module->registerMongoRepositories();
            if (!is_array($repositories) && !$repositories instanceof \Traversable)
                throw new \RuntimeException(sprintf(
                    'Error While Loading Module (%s) Repositories; Setting should be Array or Traversable. given: (%s).'
                    , $moduleName
                    , is_object($repositories) ? get_class($repositories) : gettype($repositories)
                ));

            $this->_reposRegistry()->mergeRecursive($repositories);
        }
    }

    // ..

    /**
     * Retrieve Repositories Registry
     *
     * @return ReposRegistry
     * @throws \Exception
     */
    protected function _reposRegistry()
    {
        if ($this->_reposRegistry)
            return $this->_reposRegistry;

        /** @var aSapi $application */
        $application   = $this->moduleManager->getTarget();
        $reposRegistry = $application->services()->from('/module/mongodriver/services')
            ->get(Services::ReposRegistry);

        return $this->_reposRegistry = $reposRegistry;
    }
}
