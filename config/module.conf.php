<?php
return array(
    Module\MongoDriver\Module::CONF_KEY => array(

        // Configuration of Repository Service
        // Usually Implemented with modules that implement mongo usage
        // @see aServiceRepository bellow
        \Module\MongoDriver\Services\aServiceRepository::CONF_KEY => array(
            // which client to connect and query with
            'client' => \Module\MongoDriver\MongoDriverManagementFacade::CLIENT_DEFAULT,
            'collection' => array(
                // query on which collection
                'name'    => 'name_collection',
                // ensure indexes
                'indexes' => array()
            )
        ),
        
        // Master Connection Client
        \Module\MongoDriver\MongoDriverManagementFacade::CLIENT_DEFAULT
            => array(
                ## mongodb://[username:password@]host1[:port1][,host2[:port2],...[,hostN[:portN]]][/[database][?options]]
                #- anything that is a special URL character needs to be URL encoded.
                ## This is particularly something to take into account for the password,
                #- as that is likely to have characters such as % in it.
                'host' => 'mongodb://localhost:27017',
            
                ## Required Database Name To Client Connect To
                'db'   => 'admin',
                
                ## Specifying options via the options argument will overwrite any options
                #- with the same name in the uri argument.
                'options_uri' => array(
                    /** @link https://docs.mongodb.com/manual/reference/connection-string */

                ),

                'options_driver' => array(
                    /** @link http://php.net/manual/en/mongodb-driver-manager.construct.php */
                    /** @link http://php.net/manual/en/mongodb.persistence.php#mongodb.persistence.typemaps */
                    # 'typeMap' => (array) Default type map for cursors and BSON documents.
                ),
        ),
    ),
);
