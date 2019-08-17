<?php

use Module\MongoDriver\Actions\MongoDriverAction;
use Module\MongoDriver\Services\aServiceRepository;

return [
    // Default Configurations
    aServiceRepository::class => [
        // default db name
        'db_name' => 'poirot',
        'client'  => MongoDriverAction::ClientMaster,
        // @return instanceof \MongoDB\BSON\Persistable
        #'persistable' => new \Poirot\Ioc\instance('Path\To\Service\PersistableInstance'),

        // !! this settings are an example how you can define your repo settings
        'collection'  => [
            // which client to connect and query with
            'client'  => MongoDriverAction::ClientMaster,
            // specific database for this collection
            'db_name' => 'posts',

            // query on which collection
            'name'    => 'name_collection',
            // ensure indexes
            'indexes' => [
                // Create a unique index on the "username" field
                ['key'   => ['username' => 1], 'unique' => true],
                // Create a 2dsphere index on the "loc" field with a custom name
                ['key'   => ['loc' => '2dsphere'], 'name' => 'geo'],
            ]
        ],
    ],
];
