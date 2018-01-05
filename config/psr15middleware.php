<?php
return [
    'middleware' => [
        [\Jshannon63\Psr15Middleware\exampleMiddleware::class, 'after'],
    ],
    'groups' => [
        'web' => [
        ],
        'api' => [
        ],
        'custom' => [
        ],
    ],
    'aliases' => [
    ]
];
