<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\Controller;
use App\Core\Response;
use App\Core\View;
use App\Services\Auth\AdminNonceService;
use App\Services\Auth\AdminRepository;
use App\Services\Auth\SessionGuard;
use App\Services\Auth\WalletVerifier;
use App\Support\AdminMode;
use App\Support\Flash;
use App\Support\Session;

final class AuthController extends Controller
{
    public function showLogin(): void
    {
        $notice = Flash::pull('auth_notice');
        $config = Container::get('config');
        $projectId = $config['wallet']['project_id'] ?? '';
        $rpcUrl = $config['wallet']['rpc_url'] ?? '';

        View::render('public/login', compact('notice', 'projectId', 'rpcUrl'));
    }

    public function issueNonce(): void
    {
        $nonceService = new AdminNonceService();
        $nonce = $nonceService->issueNonce();

        Response::json([
            'nonce' => $nonce,
            'message' => "AG Blog Admin Login\nNonce: {$nonce}",
        ]);
    }

    public function verify(): void
    {
        $body = file_get_contents('php://input');
        $payload = json_decode($body, true);

        if (!is_array($payload)) {
            Response::json(['error' => 'Invalid request payload.'], 400);
            return;
        }

        $nonce = $payload['nonce'] ?? '';
        $address = $payload['address'] ?? '';
        $signature = $payload['signature'] ?? '';

        if (!is_string($nonce) || !is_string($address) || !is_string($signature)) {
            Response::json(['error' => 'Missing required fields.'], 422);
            return;
        }

        $nonceService = new AdminNonceService();

        $verifier = new WalletVerifier();
        $message = "AG Blog Admin Login\nNonce: {$nonce}";

        if (!$verifier->verifyEvmSignature($address, $message, $signature)) {
            Response::json(['error' => 'Signature verification failed.'], 401);
            return;
        }

        $repository = new AdminRepository();
        $admin = $repository->findByWallet($address);

        if (!$admin) {
            Response::json(['error' => 'Wallet not authorized.'], 403);
            return;
        }

        if (!$nonceService->consume($nonce, (int)$admin['id'])) {
            Response::json(['error' => 'Nonce expired or invalid.'], 400);
            return;
        }

        $guard = new SessionGuard();
        $guard->login((int)$admin['id']);
        AdminMode::setWallet($address);
        AdminMode::disable();

        $sessionId = session_id();
        $repository->recordSession((int)$admin['id'], $sessionId, $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null);

        Response::json([
            'success' => true,
            'redirect' => '/admin/dashboard',
        ]);
    }

    public function logout(): void
    {
        Session::ensureStarted();
        $sessionId = session_id();

        $repository = new AdminRepository();
        if ($sessionId) {
            $repository->deleteSession($sessionId);
        }

        $guard = new SessionGuard();
        $guard->logout();

        Flash::set('auth_notice', 'You have been logged out.');
        $this->redirect('/admin/login');
    }
}
