<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\View;
use App\Services\Agents\AgentApiKeyRepository;
use App\Services\Agents\AgentRepository;
use App\Support\Flash;

final class ApiKeysController extends Controller
{
    private AgentRepository $agents;
    private AgentApiKeyRepository $keys;

    public function __construct(?AgentRepository $agents = null, ?AgentApiKeyRepository $keys = null)
    {
        $this->agents = $agents ?? new AgentRepository();
        $this->keys = $keys ?? new AgentApiKeyRepository();
    }

    public function index(): void
    {
        $agents = $this->agents->listAll();
        $keysByAgent = [];
        foreach ($agents as $agent) {
            $keysByAgent[(int)$agent['id']] = $this->keys->listForAgent((int)$agent['id']);
        }

        View::render('layouts/admin', [
            'title' => 'API Keys',
            'contentTemplate' => 'admin/api-keys',
            'contentData' => [
                'agents' => $agents,
                'keysByAgent' => $keysByAgent,
            ],
        ]);
    }

    public function rotate(): void
    {
        $agentId = (int)($_POST['agent_id'] ?? 0);
        $label = trim((string)($_POST['label'] ?? 'Primary'));

        $agent = $this->agents->findById($agentId);
        if (!$agent) {
            Flash::set('error', 'Agent not found.');
            $this->redirect('/admin/api-keys');
            return;
        }

        try {
            $result = $this->keys->rotate($agentId, $label === '' ? null : $label);
            $tokenMessage = sprintf('New token for %s: %s', $agent['name'], $result['token']);
            Flash::set('api_token', $tokenMessage);
            Flash::set('success', 'API token rotated successfully.');
        } catch (\Throwable $exception) {
            Flash::set('error', $exception->getMessage());
        }

        $this->redirect('/admin/api-keys');
    }

    public function show(string $agentId): void
    {
        $id = (int)$agentId;
        $agent = $this->agents->findById($id);
        if (!$agent) {
            Flash::set('error', 'Agent not found.');
            $this->redirect('/admin/api-keys');
            return;
        }

        $latestKey = $this->keys->latestActiveForAgent($id);
        $endpoint = rtrim((string)config('app.url', ''), '/') . '/api/v1/posts';

        View::render('layouts/admin', [
            'title' => 'API Guide',
            'contentTemplate' => 'admin/api-guide',
            'contentData' => [
                'agent' => $agent,
                'latestKey' => $latestKey,
                'endpoint' => $endpoint,
            ],
        ]);
    }
}
