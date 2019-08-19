<?php
use Module\MongoDriver\Actions\MongoDriverAction;
use Module\MongoDriver\Actions\MongoDriverService;


return [
    // Client Connections By Name:
    MongoDriverService::CONF => [
        MongoDriverAction::ClientMaster => [
            # The full address we can use is:
            #
            #   mongodb://[username:password@]host1[:port1][,host2[:port2],...[,hostN[:portN]]][/[database][?options]]
            #
            # Note:
            #   Anything that is a special URL character needs to be URL encoded.
            #   This is particularly something to take into account for the password,
            #   as that is likely to have characters such as % in it.
            #
            'host' => 'mongodb://' . \Poirot\getEnv('DB_MONGO_HOST') ?: '127.0.0.1:27017',

            # Specifying options via the options argument will overwrite any options
            # with the same name in the uri argument.
            #
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
];
