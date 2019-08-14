<?php
namespace Module\MongoDriver\Interfaces;


interface iObjectID
{
    /**
     * iObjectID constructor.
     * 
     * @param null|string $id
     */
    function __construct($id = null);
    
    function __toString();
}
