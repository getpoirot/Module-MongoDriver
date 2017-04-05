<?php
return array(
    Module\MongoDriver\Module::CONF_KEY => [
        ## Your extended repository settings can be add as an item into this:
        \Module\MongoDriver\Services\aServiceRepository::CONF_REPOSITORIES => [
            ## Configuration of Repository Service To Register And Retrieve From IOC,
            #- Usually Implemented with modules that implement mongo usage
            #- with specific key name as repo name.
            // @see aServiceRepository bellow
            \Module\MongoDriver\Services\aServiceRepository::class
            => [
                'collection'  => [
                    // query on which collection
                    'name'    => 'name_collection',
                    // which client to connect and query with
                    'client'  => \Module\MongoDriver\Module\MongoDriverManagementFacade::CLIENT_DEFAULT,
                    // ensure indexes
                    'indexes' => [
                        // Create a unique index on the "username" field
                        ['key'   => ['username' => 1], 'unique' => true],
                        // Create a 2dsphere index on the "loc" field with a custom name
                        ['key'   => ['loc' => '2dsphere'], 'name' => 'geo'],
                    ]
                ],
                // @return instanceof \MongoDB\BSON\Persistable
                'persistable' => new \Poirot\Ioc\instance('Path\To\Service\PersistableInstance'),
            ],
        ],

        // Client Connections By Name:
        /** @see MongoDriverManagementFacade::getClient */
        'clients' => [
            \Module\MongoDriver\Module\MongoDriverManagementFacade::CLIENT_DEFAULT
            => [
                /**
                 * Its Always Override By One Module That Setup Data Base Client Default
                 */
                ## mongodb://[username:password@]host1[:port1][,host2[:port2],...[,hostN[:portN]]][/[database][?options]]
                #- anything that is a special URL character needs to be URL encoded.
                ## This is particularly something to take into account for the password,
                #- as that is likely to have characters such as % in it.
                'host' => 'mongodb://localhost:27017',

                ## Required Database Name To Client Connect To
                'db'   => 'admin',

                ## Specifying options via the options argument will overwrite any options
                #- with the same name in the uri argument.
                'options_uri' => [
                    /** @link https://docs.mongodb.com/manual/reference/connection-string */

                ],

                'options_driver' => [
                    /** @link http://php.net/manual/en/mongodb-driver-manager.construct.php */
                    /** @link http://php.net/manual/en/mongodb.persistence.php#mongodb.persistence.typemaps */
                    # 'typeMap' => (array) Default type map for cursors and BSON documents.
                ],
            ],
        ],
    ],
);
