<?php
declare(strict_types=1);

namespace App\Services\Auth;

use App\Core\Database;
use App\Core\Container;
use PDO;

final class AdminNonceService
{
    private PDO $db;
    private int $ttl;

    public function __construct()
    {
        $this->db = Database::connection();
        $config = Container::get('config');
        $this->ttl = (int)($config['wallet']['nonce_ttl'] ?? 300);
    }

    public function issueNonce(): string
    {
        $nonce = bin2hex(random_bytes(32));
        $expiresAt = (new \DateTimeImmutable('+' . $this->ttl . ' seconds'))->format('Y-m-d H:i:s');

        $stmt = $this->db->prepare('INSERT INTO admin_nonces (nonce, expires_at) VALUES (:nonce, :expires_at)');
        $stmt->execute([
            'nonce' => $nonce,
            'expires_at' => $expiresAt,
        ]);

        return $nonce;
    }

    public function consume(string $nonce, int $adminId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE admin_nonces SET admin_id = :admin_id, consumed_at = NOW()
             WHERE nonce = :nonce AND consumed_at IS NULL AND expires_at >= NOW()'
        );

        $stmt->execute([
            'admin_id' => $adminId,
            'nonce' => $nonce,
        ]);

        return $stmt->rowCount() > 0;
    }
}
