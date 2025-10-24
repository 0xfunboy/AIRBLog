<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\View;
use App\Services\Posts\AgentPostRepository;

final class DashboardController extends Controller
{
    private AgentPostRepository $posts;

    public function __construct(?AgentPostRepository $posts = null)
    {
        $this->posts = $posts ?? new AgentPostRepository();
    }

    public function redirect(): void
    {
        $this->redirect('/admin/dashboard');
    }

    public function index(): void
    {
        $stats = [
            'total' => $this->posts->countAll(),
            'published' => $this->posts->countByStatus('published'),
            'pending' => $this->posts->countByStatus('pending'),
        ];

        $recent = $this->posts->recent(8);

        View::render('layouts/admin', [
            'title' => 'Dashboard',
            'contentTemplate' => 'admin/dashboard',
            'contentData' => [
                'stats' => $stats,
                'recentPosts' => $recent,
            ],
        ]);
    }
}
