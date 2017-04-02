<?php
namespace Module\MongoDriver\Model;

use MongoDB\BSON\Persistable;
use Poirot\Std\Struct\DataOptionsOpen;


class aPersistable
    extends DataOptionsOpen
    implements Persistable
{
    use tPersistable;

}
