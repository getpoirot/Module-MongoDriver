<?php
use Module\MongoDriver\Actions;

return [
    'services' => [
        Actions::Driver => Actions\MongoDriverService::class,
    ],
];
