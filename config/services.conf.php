<?php
use Module\MongoDriver\Services;

return [
    'implementations' => [
        Services::ReposRegistry => Services\ReposRegistry::class,
    ],
    'services' => [
        Services::ReposRegistry => Services\ReposRegistry::class,
    ],
];
