<?php

declare(strict_types=1);

/**
 * Application Configuration
 */

return [
    'name'     => $_ENV['APP_NAME']     ?? 'Peace Seafood',
    'env'      => $_ENV['APP_ENV']      ?? 'local',
    'debug'    => ($_ENV['APP_DEBUG']   ?? 'true') === 'true',
    'url'      => $_ENV['APP_URL']      ?? 'http://localhost',
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'Asia/Jakarta',

    'jwt' => [
        'secret'     => $_ENV['JWT_SECRET']     ?? 'change-this-secret',
        'algorithm'  => $_ENV['JWT_ALGORITHM']  ?? 'HS256',
        'expiration' => (int)($_ENV['JWT_EXPIRATION'] ?? 1800), // 30 minutes in seconds
    ],

    'upload' => [
        'max_size'      => (int)($_ENV['UPLOAD_MAX_SIZE'] ?? 5242880),
        'allowed_types' => explode(',', $_ENV['UPLOAD_ALLOWED_TYPES'] ?? 'jpg,jpeg,png,gif,pdf'),
        'path'          => __DIR__ . '/../storage/uploads/',
    ],

    'export' => [
        'max_rows' => (int)($_ENV['EXPORT_MAX_ROWS'] ?? 10000),
        'pdf_path' => __DIR__ . '/../storage/exports/pdf/',
        'xls_path' => __DIR__ . '/../storage/exports/excel/',
    ],

    'session' => [
        'timeout_minutes' => (int)($_ENV['SESSION_TIMEOUT_MINUTES'] ?? 30),
        'name'            => $_ENV['SESSION_NAME'] ?? 'PEACE_SEAFOOD_SESSION',
        'cookie_lifetime' => (int)($_ENV['SESSION_COOKIE_LIFETIME'] ?? 1800), // 30 minutes in seconds
        'cookie_path'     => '/',
        'cookie_domain'   => $_ENV['SESSION_COOKIE_DOMAIN'] ?? '',
        'cookie_secure'   => ($_ENV['SESSION_COOKIE_SECURE'] ?? 'false') === 'true',
        'cookie_httponly' => true,
        'cookie_samesite' => $_ENV['SESSION_COOKIE_SAMESITE'] ?? 'Strict',
    ],

    'cors' => [
        'origin' => $_ENV['CORS_ORIGIN'] ?? 'http://localhost',
    ],

    'log' => [
        'channel' => $_ENV['LOG_CHANNEL'] ?? 'single',
        'level'   => $_ENV['LOG_LEVEL']   ?? 'debug',
        'path'    => __DIR__ . '/../storage/logs/app.log',
    ],
];
