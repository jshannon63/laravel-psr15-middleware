<?php
return [
    'middleware' => [
        [\Jshannon63\Psr15Middleware\exampleMiddleware::class, 'prepend', 'after'],
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
