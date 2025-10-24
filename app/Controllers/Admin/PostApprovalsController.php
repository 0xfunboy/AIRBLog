<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\View;
use App\Services\Auth\SessionGuard;
use App\Services\Posts\AgentPostRepository;
use App\Services\Posts\PostService;
use App\Support\Flash;

final class PostApprovalsController extends Controller
{
    private AgentPostRepository $posts;
    private PostService $service;

    public function __construct(?AgentPostRepository $posts = null, ?PostService $service = null)
    {
        $this->posts = $posts ?? new AgentPostRepository();
        $this->service = $service ?? new PostService();
    }

    public function index(): void
    {
        $allowed = ['pending', 'published', 'rejected', 'all'];
        $status = strtolower((string)($_GET['status'] ?? 'pending'));
        if (!in_array($status, $allowed, true)) {
            $status = 'pending';
        }

        $posts = $this->posts->listByStatus($status === 'all' ? null : $status, 50);

        View::render('layouts/admin', [
            'title' => 'Posts',
            'contentTemplate' => 'admin/posts',
            'contentData' => [
                'status' => $status,
                'posts' => $posts,
            ],
        ]);
    }

    public function approve(int $id): void
    {
        $guard = new SessionGuard();
        $adminId = $guard->id();
        if (!$adminId) {
            Flash::set('error', 'Session expired. Please log in again.');
            $this->redirect('/admin/login');
            return;
        }

        try {
            $post = $this->service->approve($id, (int)$adminId);
            Flash::set('success', sprintf('Post #%d approved.', $post['id']));
        } catch (\Throwable $exception) {
            Flash::set('error', $exception->getMessage());
        }

        $this->redirect('/admin/posts');
    }

    public function reject(int $id): void
    {
        $guard = new SessionGuard();
        $adminId = $guard->id();
        if (!$adminId) {
            Flash::set('error', 'Session expired. Please log in again.');
            $this->redirect('/admin/login');
            return;
        }

        try {
            $post = $this->service->reject($id, (int)$adminId);
            Flash::set('success', sprintf('Post #%d rejected.', $post['id']));
        } catch (\Throwable $exception) {
            Flash::set('error', $exception->getMessage());
        }

        $this->redirect('/admin/posts');
    }
}
