<?php
use App\Core\View;

/** @var string|null $notice */
/** @var string $projectId */
/** @var string $rpcUrl */

View::render('layouts/public', [
    'title' => 'AG Blog Admin Login',
    'contentTemplate' => 'public/login-content',
    'contentData' => [
        'notice' => $notice ?? null,
        'projectId' => $projectId ?? '',
        'rpcUrl' => $rpcUrl ?? '',
    ],
]);
