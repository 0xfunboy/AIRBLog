<?php
declare(strict_types=1);

namespace App\Controllers\System;

use App\Core\Controller;

final class HealthController extends Controller
{
    public function show(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'ok'], JSON_THROW_ON_ERROR);
    }
}
