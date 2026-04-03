<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        env('FRONTEND_URL', 'http://localhost:5173'),
        'http://localhost:3000',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:3000',
    ],
    'allowed_origins_patterns' => ['http://192.168.*.*:*'],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 3600,
    'supports_credentials' => true,
];
