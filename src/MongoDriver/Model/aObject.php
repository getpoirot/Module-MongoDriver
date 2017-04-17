<?php
namespace Module\MongoDriver\Model;

use Poirot\Std\Interfaces\Pact\ipConfigurable;
use Poirot\Std\Struct\DataOptionsOpen;


/**
 * @deprecated use Std aValueObject
 *
 */
abstract class aObject
    extends DataOptionsOpen
    implements ipConfigurable
{

    // Implement Configurable

    /**
     * Build Object With Provided Options
     *
     * @param array $options        Associated Array
     * @param bool  $throwException Throw Exception On Wrong Option
     *
     * @return array Remained Options (if not throw exception)
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    function with(array $options, $throwException = false)
    {
        $this->import($options);
    }

    /**
     * Load Build Options From Given Resource
     *
     * - usually it used in cases that we have to support
     *   more than once configure situation
     *   [code:]
     *     Configurable->with(Configurable::withOf(path\to\file.conf))
     *   [code]
     *
     * !! With this The classes that extend this have to
     *    implement desired parse methods
     *
     * @param array|mixed $optionsResource
     * @param array $_
     *        usually pass as argument into ::with if self instanced
     *
     * @throws \InvalidArgumentException if resource not supported
     * @return array
     */
    static function parseWith($optionsResource, array $_ = null)
    {
        if (!static::isConfigurableWith($optionsResource))
            throw new \InvalidArgumentException(sprintf(
                'Invalid Configuration Resource provided; given: (%s).'
                , \Poirot\Std\flatten($optionsResource)
            ));


        // ..

        if ($optionsResource instanceof \Traversable)
            $optionsResource = \Poirot\Std\cast($optionsResource)->toArray();
        elseif ($optionsResource instanceof \stdClass)
            $optionsResource = \Poirot\Std\toArrayObject($optionsResource);

        return $optionsResource;
    }

    /**
     * Is Configurable With Given Resource
     * @ignore
     *
     * @param mixed $optionsResource
     *
     * @return boolean
     */
    static function isConfigurableWith($optionsResource)
    {
        return is_array($optionsResource)
            || $optionsResource instanceof \Traversable
            || $optionsResource instanceof \stdClass
        ;
    }
}
