<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\View;
use App\Services\Agents\AgentRepository;
use App\Support\Flash;

final class AgentsController extends Controller
{
    private AgentRepository $agents;

    public function __construct(?AgentRepository $agents = null)
    {
        $this->agents = $agents ?? new AgentRepository();
    }

    public function index(): void
    {
        $agents = $this->agents->listAll();
        View::render('layouts/admin', [
            'title' => 'Agents',
            'contentTemplate' => 'admin/agents',
            'contentData' => [
                'agents' => $agents,
                'statuses' => ['Live', 'In Development'],
            ],
        ]);
    }

    public function store(): void
    {
        $payload = $this->sanitize($_POST);

        if ($payload['error']) {
            Flash::set('error', $payload['error']);
            $this->redirect('/admin/agents');
            return;
        }

        if ($this->agents->slugExists($payload['data']['slug'])) {
            Flash::set('error', 'Slug already in use.');
            $this->redirect('/admin/agents');
            return;
        }

        $this->agents->create($payload['data']);
        Flash::set('success', 'Agent created successfully.');
        $this->redirect('/admin/agents');
    }

    public function update(int $id): void
    {
        $existing = $this->agents->findById($id);
        if (!$existing) {
            Flash::set('error', 'Agent not found.');
            $this->redirect('/admin/agents');
            return;
        }

        $payload = $this->sanitize($_POST);
        if ($payload['error']) {
            Flash::set('error', $payload['error']);
            $this->redirect('/admin/agents');
            return;
        }

        if ($this->agents->slugExists($payload['data']['slug'], $id)) {
            Flash::set('error', 'Slug already in use.');
            $this->redirect('/admin/agents');
            return;
        }

        $this->agents->update($id, $payload['data']);
        Flash::set('success', 'Agent updated.');
        $this->redirect('/admin/agents');
    }

    /**
     * @param array<string,mixed> $input
     * @return array{data:array<string,mixed>,error:?string}
     */
    private function sanitize(array $input): array
    {
        $name = trim((string)($input['name'] ?? ''));
        $slug = $this->normalizeSlug((string)($input['slug'] ?? ''));
        $chain = trim((string)($input['chain'] ?? ''));
        $status = trim((string)($input['status'] ?? 'Live'));

        if ($name === '' || $slug === '' || $chain === '') {
            return ['data' => [], 'error' => 'Name, slug, and chain are required.'];
        }

        if (!in_array($status, ['Live', 'In Development'], true)) {
            $status = 'Live';
        }

        $data = [
            'name' => $name,
            'slug' => $slug,
            'chain' => $chain,
            'status' => $status,
            'is_visible' => isset($input['is_visible']) ? 1 : 0,
            'site_url' => trim((string)($input['site_url'] ?? '')),
            'image_url' => trim((string)($input['image_url'] ?? '')),
            'summary' => trim((string)($input['summary'] ?? '')),
        ];

        return ['data' => $data, 'error' => null];
    }

    private function normalizeSlug(string $value): string
    {
        $value = strtolower(trim($value));
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = preg_replace('/[^a-z0-9]+/i', '-', $value) ?? '';
        return trim($value, '-');
    }
}
