<?php
namespace Module\MongoDriver\Actions;

use \MongoDB;

use Poirot\Std\aConfigurable;

/**
 * Accessible As a Service:
 *   $mongoDriver = \Module\MongoDriver\Actions::Driver();
 *
 *   $db = $mongoDriver->getClient('master')
 *      ->selectDatabase($db);
 *
 */

class MongoDriverAction
    extends aConfigurable
{
    const CLIENT_DEFAULT = 'master';
    const SELECT_DB_FROM_CONFIG = 'db_from_merged_config';
    
    protected $lazyClientOptions = array(
        # 'clientName' => (array) options,
    );

    protected $clients = array(
        # 'clientName' => \MongoDb\Client,
    );


    /**
     * @return $this
     */
    function __invoke()
    {
        return $this;
    }


    // Options:

    /**
     * Add Client Connection
     *
     * Options:
     *    'db' => (string) default database to query on 
     * 
     * @param string         $clientName
     * @param MongoDB\Client $clientMongo
     *
     * @return $this
     * @throws \Exception
     */
    function setClient($clientName, MongoDB\Client $clientMongo)
    {
        if ($this->hasClient($clientName))
            throw new \Exception(sprintf('Client with name (%s) already exists and cant be replaced.', $clientName));


        $this->clients[$clientName] = $clientMongo;
        return $this;
    }

    /**
     * Attain Client By Name
     *
     * @param string $clientName
     *
     * @return MongoDB\Client
     * @throws \Exception
     */
    function getClient($clientName)
    {
        if (! isset($this->clients[$clientName]) )
            // if not client constructed look for lazy options:
            $this->clients[$clientName] = $this->_attainClient($clientName);


        return $this->clients[$clientName];
    }

    /**
     * Has Client Connection?
     *
     * @param string $clientName
     *
     * @return boolean
     */
    function hasClient($clientName)
    {
        if (! isset($this->clients[$clientName]) ) {
            // Lookup for lazy client options
            try {
                $exists = true;
                $this->getClient($clientName);
            } catch (\Exception $e) {
                $exists = false;
            }

            return $exists;
        }

        $exists = array_key_exists($clientName, $this->lazyClientOptions);
        return $exists;
    }


    // Implement Configurable:

    /**
     * Build Object With Provided Options
     *
     * @param array|\Traversable $options Associated Array
     * @param bool $throwException Throw Exception On Wrong Option
     *
     * options:
     *
     * 'clients' => [
     *    // Its Always Override By One Module That Setup Data Base Client Default
     *    'master' => [
     *       ## mongodb://[username:password@]host1[:port1][,host2[:port2],...[,hostN[:portN]]][/[database][?options]]
     *       #- anything that is a special URL character needs to be URL encoded.
     *       ## This is particularly something to take into account for the password,
     *       #- as that is likely to have characters such as % in it.
     *       'host' => 'mongodb://localhost:27017',
     *
     *       ## Required Database Name To Client Connect To
     *       'db'   => 'admin',
     *
     *       ## Specifying options via the options argument will overwrite any options
     *       #- with the same name in the uri argument.
     *       'options_uri' => [
     *           // @link https://docs.mongodb.com/manual/reference/connection-string
     *       ],
     *
     *       'options_driver' => [
     *           // @link http://php.net/manual/en/mongodb-driver-manager.construct.php
     *           // @link http://php.net/manual/en/mongodb.persistence.php#mongodb.persistence.typemaps
     *
     *          # 'typeMap' => (array) Default type map for cursors and BSON documents.
     *      ],
     *  ...
     *
     * @return $this
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    function with(array $options, $throwException = false)
    {
        if (isset($options['clients']))
            $this->lazyClientOptions = array_merge($this->lazyClientOptions, $options['clients']);

        
        return $this;
    }


    // ..

    /**
     * Attain Client Instance from LazyConfigs
     *
     * @param $clientName
     *
     * @return MongoDB\Client
     * @throws \Exception
     */
    protected function _attainClient($clientName)
    {
        if (! isset($this->lazyClientOptions[$clientName]) )
            throw new \Exception(sprintf('MongoDB Client (%s) not Registered.', $clientName));


        $conf = $this->lazyClientOptions[$clientName];
        if (! isset($conf['host']) )
            throw new \Exception(sprintf(
                '"host" Option for Client (%s) not given.'
                , $clientName
                , \Poirot\Std\flatten($conf)
            ));

        $uri        = $conf['host'];
        $uriOptions = (isset($conf['options_uri']))    ? $conf['options_uri']    : array();
        $options    = (isset($conf['options_driver'])) ? $conf['options_driver'] : array();

        $client = new MongoDB\Client($uri, $uriOptions, $options);
        return $client;
    }
}
