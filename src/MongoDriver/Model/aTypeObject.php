<?php
namespace Module\MongoDriver\Model;

use MongoDB\BSON\Persistable;
use Poirot\Std\Traits\tConfigurableSetter;


abstract class aTypeObject
    extends aObject
    implements Persistable
{
    use tConfigurableSetter, tPersistable{
        tPersistable::bsonSerialize   as protected _t__bsonSerialize;
        tPersistable::bsonUnserialize as protected _t__bsonUnserialize;
    }

    const TYPE = 'define_type';


    // Implement Persistable

    function bsonSerialize()
    {
        $arr = $this->_t__bsonSerialize();
        return [ static::TYPE => $arr ];
    }

    function bsonUnserialize(array $data)
    {
        if (!isset($data[static::TYPE]))
            throw new \Exception(sprintf(
                'Invalid Type Of (%s).'
                , static::TYPE
            ));

        $this->_t__bsonUnserialize($data[static::TYPE]);
    }
}
