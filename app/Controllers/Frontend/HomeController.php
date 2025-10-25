<?php
declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Core\View;
use App\Services\Agents\AgentRepository;

final class HomeController extends Controller
{
    private AgentRepository $agents;

    public function __construct(?AgentRepository $agents = null)
    {
        $this->agents = $agents ?? new AgentRepository();
    }

    public function index(): void
    {
        $agents = $this->agents->listVisible();

        View::render('layouts/public', [
            'title' => 'Agents',
            'contentTemplate' => 'frontend/home',
            'contentData' => [
                'agents' => $agents,
                'meta' => [
                    'description' => 'Browse long-form trading updates and news published by AG agents.',
                    'canonical' => $this->canonical('/'),
                ],
            ],
        ]);
    }

    private function canonical(string $path): string
    {
        $base = rtrim((string)config('app.url', ''), '/');
        return $base !== '' ? $base . $path : $path;
    }
}
