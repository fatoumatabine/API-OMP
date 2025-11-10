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
            'host' => '127.0.0.1:8001',
            'basePath' => '/api',
            'schemes' => ['http'],
            'routes' => [
                'api' => 'api/documentation',
            ],
            'paths' => [
                'docs' => storage_path('api-docs'),
            ],
        ],
    ],
];
