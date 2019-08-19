<?php
namespace Module\MongoDriver\Actions;

use \MongoDB;
use Poirot\Std\aConfigurable;
use Poirot\Std\Type\StdArray;
use Poirot\Std\Type\StdTravers;


/**
 * Connection manager and factory to get database and collection instances.
 *
 *
 * Accessible As a Service:
 *   $mongoDriver = \Module\MongoDriver\Actions::Driver();
 *
 *   $db = $mongoDriver->client('master')
 *      ->selectDatabase($db);
 *
 */
class MongoDriverAction
    extends aConfigurable
{
    const ClientMaster = 'master';

    protected $lazyClientOptions = [
        # 'clientName' => (array) options,
    ];

    protected $clients = [
        # 'clientName' => \MongoDb\Client,
    ];


    /**
     * @return $this
     */
    function __invoke()
    {
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
    function client($clientName = self::ClientMaster)
    {
        if (! isset($this->clients[$clientName]) )
            // if not client constructed look for lazy options:
            $this->clients[$clientName] = $this->_attainClient($clientName);


        return $this->clients[$clientName];
    }

    /**
     * Add Client Connection
     *
     * @param string         $clientName
     * @param MongoDB\Client $clientMongo
     *
     * @return $this
     * @throws \Exception
     */
    function setClient($clientName, MongoDB\Client $clientMongo)
    {
        if ( $this->hasClient($clientName) )
            throw new \Exception(sprintf('Client with name (%s) already exists and cant be replaced.', $clientName));


        $this->clients[$clientName] = $clientMongo;
        return $this;
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
                $this->client($clientName);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }

        return isset($this->lazyClientOptions[$clientName]);
    }


    // Implement Configurable:

    /**
     * @inheritdoc
     */
    function with(array $options, $throwException = true)
    {
        // ensure options are array recursively
        $options = StdTravers::of($options)->toArray(null, true);
        if ($throwException) {
            foreach ($options as $clientName => $conf)
                if (! isset($conf['host']) )
                    throw new \InvalidArgumentException(sprintf(
                        '"host" Option for Client (%s) not given.'
                        , $clientName
                    ));
        }


        $this->lazyClientOptions = array_merge($this->lazyClientOptions, $options);
        return $this;
    }

    // ..

    /**
     * Attain Client Instance from LazyConfigs
     *
     * @param string $clientName
     *
     * @return MongoDB\Client
     * @throws \Exception
     */
    private function _attainClient($clientName)
    {
        if (! isset($this->lazyClientOptions[$clientName]) )
            throw new \Exception(sprintf('MongoDB Client (%s) not Registered.', $clientName));


        $conf = $this->lazyClientOptions[$clientName];

        $uri        = $conf['host'];
        $uriOptions = $conf['options_uri']    ?? [];
        $options    = $conf['options_driver'] ?? [];

        return new MongoDB\Client($uri, $uriOptions, $options);
    }
}
