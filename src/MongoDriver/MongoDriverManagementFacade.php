<?php
namespace Module\MongoDriver;

use \MongoDB;
use Poirot\Std\aConfigurable;

class MongoDriverManagementFacade
    extends aConfigurable
{
    const CLIENT_DEFAULT = 'master';
    
    static protected $CLIENT_DEFAULT = self::CLIENT_DEFAULT;

    protected $clients = array(
        # 'clientName' => MongoDB\Client,
    );


    /**
     * Attain Connection Client
     *
     * @param string $clientName
     *
     * @return MongoDB\Client
     * @throws \Exception
     */
    function byClient($clientName = self::CLIENT_DEFAULT)
    {
        if (!$this->hasClient($clientName))
            throw new \Exception(sprintf('Client with name (%s) not exists.', $clientName));

        $client = $this->clients[$clientName];
        return $client;
    }

    /**
     * Add Client Connection
     *
     * @param MongoDB\Client $clientMongo
     * @param string $clientName
     *
     * @return $this
     * @throws \Exception
     */
    function addClient(MongoDB\Client $clientMongo, $clientName)
    {
        if ($this->hasClient($clientName))
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
        return array_key_exists($clientName, $this->clients);
    }

    /**
     * Set Default Client Name
     *
     * @param string $clientName
     */
    static function setDefaultClient($clientName = self::CLIENT_DEFAULT)
    {
        self::$CLIENT_DEFAULT = $clientName;
    }


    // Implement Syntactical:

    function __get($name)
    {
        return $this->byClient($name);
    }

    function __set($name, $value)
    {
        return $this->addClient($value, $name);
    }

    function __call($name, $arguments)
    {
        $masterClient = $this->byClient();
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
        foreach ($options as $name => $conf) {
            $uri        = $conf['host'];
            $uriOptions = (isset($conf['options_uri'])) ? $conf['options_uri'] : array();
            $options    = (isset($conf['options'])) ? $conf['options'] : array();

            $this->addClient(new MongoDB\Client($uri, $uriOptions, $options), $name);
        }
    }
}
