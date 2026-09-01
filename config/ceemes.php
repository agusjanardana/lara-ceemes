<?php

declare(strict_types=1);

return [
    'admin' => [
        'prefix' => env('CEEMES_ADMIN_PREFIX', 'admin'),
        'middleware' => [
            'web',
            'auth',
        ],
        'gate' => 'access-ceemes',
    ],

    'auth' => [
        'routes' => env('CEEMES_AUTH_ROUTES', true),
        'login_route_name' => 'login',
    ],

    'homepage' => [
        'enabled' => env('CEEMES_HOMEPAGE_ENABLED', true),
    ],

    'public_routing' => [
        'enabled' => env('CEEMES_PUBLIC_ROUTING', true),
        'middleware' => ['web'],
    ],

    'views' => [
        'auto_scaffold' => env('CEEMES_AUTO_SCAFFOLD_VIEWS', true),
        'path' => null,
    ],

    'cache' => [
        'enabled' => env('CEEMES_CACHE_ENABLED', true),
        'store' => env('CEEMES_CACHE_STORE'),
        'ttl' => (int) env('CEEMES_CACHE_TTL', 3600),
        'prefix' => 'ceemes',
    ],

    'media' => [
        'disk' => env('CEEMES_MEDIA_DISK', 'public'),
        'directory' => env('CEEMES_MEDIA_DIRECTORY', 'ceemes'),
        'max_upload_size' => (int) env('CEEMES_MEDIA_MAX_UPLOAD_SIZE', 10240),
        'allowed_mimes' => [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/svg+xml',
            'application/pdf',
        ],
    ],

    'users' => [
        'model' => null,
        'table' => 'users',
        'key' => 'id',
        'key_type' => 'integer',
        'foreign_keys' => true,
        'name_attribute' => 'name',
        'email_attribute' => 'email',
        'password_attribute' => 'password',
    ],
];
