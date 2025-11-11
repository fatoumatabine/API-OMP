<?php

return [
    'default' => 'default',
    'documentations' => [
        'default' => [
            'api' => [
                'title' => 'Orange Money API',
                'description' => 'API pour les services financiers mobile',
                'version' => '1.0.0',
            ],
            'host' => env('APP_URL'),
            'basePath' => '/api/v1',
            'schemes' => ['https'],
            'routes' => [
                'api' => 'api/documentation',
            ],
            'paths' => [
                'docs' => storage_path('api-docs'),
            ],
        ],
    ],
];
