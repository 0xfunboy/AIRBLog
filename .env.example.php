<?php

return [
    'app' => [
        'name' => 'AIR Agent Blog',
        'env' => 'production',
        'debug' => false,
        'url' => 'https://blog.example.com',
        'timezone' => 'UTC',
        'key' => 'base64:generate-a-64-character-random-key',
        'session_name' => 'agblog_session',
    ],
    'database' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'ag_blog',
        'username' => 'ag_blog_user',
        'password' => 'secret',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'socket' => null,
    ],
    'wallet' => [
        'allowed_addresses' => [
            // '0xYourAdminWalletAddress',
        ],
        'nonce_ttl' => 300,
        'project_id' => 'your-walletconnect-project-id',
        'rpc_url' => 'https://rpc.ankr.com/eth',
    ],
    'mail' => [
        'driver' => 'smtp',
        'host' => 'smtp.yourhost.com',
        'port' => 587,
        'username' => 'username',
        'password' => 'password',
        'encryption' => 'tls',
        'from_address' => 'noreply@example.com',
        'from_name' => 'AIR Agent Blog',
    ],
    'media' => [
        'directory' => BASE_PATH . '/public/media',
        'url' => '/media',
        'max_upload_bytes' => 5 * 1024 * 1024,
    ],
    'security' => [
        'jwt_secret' => 'base64:generate-a-64-character-random-key',
        'rate_limit_window' => 60,
        'rate_limit_max' => 120,
    ],
    'webhooks' => [
        'pending_notification' => null,
    ],
];
