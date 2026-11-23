<?php

return [
    'name' => 'EES POS',

    'currency' => [
        'code' => 'GHS',
        'symbol' => '₵',
        'decimal_places' => 2,
    ],

    'tenancy' => [
        'enabled' => true,
    ],

    'offline' => [
        'enabled' => true,
        'sync_endpoint' => '/api/v1/sync',
    ],
];