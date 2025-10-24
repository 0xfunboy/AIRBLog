<?php

return [
    'app' => [
        'name' => 'AIRewardrop',
        'env' => 'production',
        'debug' => false,
        'url' => 'https://airewardrop.xyz',
        'timezone' => 'UTC',
        'key' => 'base64:generate-a-64-character-random-key',
        'session_name' => 'aire_session',
    ],
    'database' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'airewardrop',
        'username' => 'aire_user',
        'password' => 'secret',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
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
        'from_address' => 'noreply@airewardrop.xyz',
        'from_name' => 'AIRewardrop',
    ],
];
