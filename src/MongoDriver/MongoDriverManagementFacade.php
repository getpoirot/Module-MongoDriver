<?php
namespace Module\MongoDriver;

use \MongoDB;

use Poirot\Std\aConfigurable;

/**
 * Accessible As a Service:
 *   $mongoDriver = $services->get('/modules/mongodriver');
 *   
 *   ## master connection 
 *   $mongoDriver->selectCollection('local', 'startup_log')->count();
 *   
 *   ## defined connection names
 *   $mongoDriver->replica->selectCollection('local', 'startup_log')->count();
 *   $mongoDriver->serverN->...
 *   
 */

class MongoDriverManagementFacade
    extends aConfigurable
{
    const CLIENT_DEFAULT = 'master';
    
    protected $lazyClientOptions = array(
        # 'clientName' => (array) options,
    );

    protected $clients = array(
        # 'clientName' => \MongoDb\Client,
    );


    /**
     * Attain Connection Client
     *
     * - query on default database defined by client options
     *
     * @param string $clientName
     * @param string $db
     *
     * @return MongoDB\Database
     * @throws \Exception
     */
    function query($clientName = self::CLIENT_DEFAULT, $db = 'admin')
    {
        if (!$this->hasClient($clientName))
            throw new \Exception(sprintf('Client with name (%s) not exists.', $clientName));


        $client = $this->getClient($clientName);

        if (isset($this->lazyClientOptions[$clientName])
            && is_array($this->lazyClientOptions[$clientName])
            && isset($this->lazyClientOptions[$clientName]['db'])
        )
            $db = $this->lazyClientOptions[$clientName]['db'];
        
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
            $uriOptions = (isset($conf['options_uri'])) ? $conf['options_uri'] : array();
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


    // Implement Syntactical:

    function __get($name)
    {
        return $this->query($name);
    }

    function __set($name, $value)
    {
        return $this->addClient($value, $name);
    }

    function __call($name, $arguments)
    {
        $masterClient = $this->query();
        return call_user_func_array(array($masterClient, $name), $arguments);
    }


    // Implement Configurable:

    /**
     * Build Object With Provided Options
     *
     * @param array|\Traversable $options Associated Array
     * @param bool $throwException Throw Exception On Wrong Option
     *
     * @return $this
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    function with($options, $throwException = false)
    {
        if ($options instanceof \Traversable)
            $options = iterator_to_array($options);

        $this->lazyClientOptions = array_merge($this->lazyClientOptions, $options);
        return $this;
    }
}
