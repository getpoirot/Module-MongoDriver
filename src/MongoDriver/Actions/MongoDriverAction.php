<?php
namespace Module\MongoDriver\Actions;

use \MongoDB;

use Poirot\Std\aConfigurable;

/**
 * Accessible As a Service:
 *   $mongoDriver = \Module\MongoDriver\Actions\IOC::Driver();
 *   
 *   ## master connection 
 *   $mongoDriver->selectCollection('local', 'startup_log')->count();
 *   
 *   ## defined connection names
 *   $mongoDriver->replica->selectCollection('local', 'startup_log')->count();
 *   $mongoDriver->serverN->...
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

    /**
     * Attain Connection Client
     *
     * - query on default database defined by client options
     *
     * @param string $db
     * @param string $clientName
     *
     * @return MongoDB\Database
     * @throws \Exception
     */
    function database($db = self::SELECT_DB_FROM_CONFIG, $clientName = self::CLIENT_DEFAULT)
    {
        if (!$this->hasClient($clientName))
            throw new \Exception(sprintf('Client with name (%s) not exists.', $clientName));


        $client = $this->getClient($clientName);
        
        if ($db == self::SELECT_DB_FROM_CONFIG && isset($this->lazyClientOptions[$clientName])
            && is_array($this->lazyClientOptions[$clientName])
            && isset($this->lazyClientOptions[$clientName]['db'])
        ) {
            // Retrieve Default DB Config to Connect Client To ...
            $db = $this->lazyClientOptions[$clientName]['db'];
        }
        
        
        if ($db === null)
            throw new \Exception(sprintf(
                'Default Database name for Client (%s) not defined.'
                , $clientName 
            ));
        
        return $client->selectDatabase($db);
    }

    /**
     * Add Client Connection
     *
     * Options:
     *    'db' => (string) default database to query on 
     * 
     * @param MongoDB\Client $clientMongo
     * @param string         $clientName
     * @param array          $options
     * @return $this
     * @throws \Exception
     */
    function addClient(MongoDB\Client $clientMongo, $clientName, array $options = null)
    {
        if ($this->hasClient($clientName))
            throw new \Exception(sprintf('Client with name (%s) already exists and cant be replaced.', $clientName));

        $this->clients[$clientName] = $clientMongo;
        if ($options)
            $this->with(array($clientName => $options));
        
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
        if (isset($this->clients[$clientName]))
            return $this->clients[$clientName];

        if (isset($this->lazyClientOptions[$clientName])) {
            // if not client constructed look for lazy options:
            $conf = $this->lazyClientOptions[$clientName];
            
            if (!isset($conf['host']))
                throw new \Exception(sprintf(
                    'Options for Client (%s) need Host at least. given: (%s)'
                    , $clientName
                    , \Poirot\Std\flatten($conf)
                ));
            
            $uri        = $conf['host'];
            $uriOptions = (isset($conf['options_uri']))    ? $conf['options_uri']    : array();
            $options    = (isset($conf['options_driver'])) ? $conf['options_driver'] : array();

            $client = new MongoDB\Client($uri, $uriOptions, $options);
            return $this->clients[$clientName] = $client;
        }

        throw new \Exception(sprintf('MongoDB Client (%s) not Registered.', $clientName));
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
        $exists = array_key_exists($clientName, $this->lazyClientOptions);
        if (!$exists) {
            // Lookup for lazy client options
            try {
                $exists = true;
                $this->getClient($clientName);
            } catch (\Exception $e) {
                $exists = false;
            }
        }

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
     *    \Module\MongoDriver\Module\MongoDriverManagementFacade::CLIENT_DEFAULT => [
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
        if (isset($options['clients'])) {
            $this->lazyClientOptions = array_merge($this->lazyClientOptions, $options['clients']);
        }
        
        return $this;
    }
}
