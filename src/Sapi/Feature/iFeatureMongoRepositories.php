<?php
namespace Module\MongoDriver\Sapi\Feature;

use Poirot\Application\Interfaces\Sapi\iFeatureSapiModule;


interface iFeatureMongoRepositories
    extends iFeatureSapiModule
{
    /**
     * Return Available Mongo Repositories
     *
     * @return array
     */
    function registerMongoRepositories();
}
