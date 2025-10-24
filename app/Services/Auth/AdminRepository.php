<?php
declare(strict_types=1);

namespace App\Services\Auth;

use App\Core\Database;
use PDO;

final class AdminRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function findByWallet(string $address): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM admins WHERE LOWER(wallet_address) = LOWER(:address) LIMIT 1');
        $stmt->execute(['address' => $address]);
        $admin = $stmt->fetch();

        return $admin ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM admins WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $admin = $stmt->fetch();

        return $admin ?: null;
    }

    public function recordSession(int $adminId, string $sessionToken, ?string $ip, ?string $userAgent, int $ttlMinutes = 60): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO admin_sessions (admin_id, session_token, ip_address, user_agent, expires_at)
             VALUES (:admin_id, :token, :ip, :agent, :expires_at)'
        );

        $expires = (new \DateTimeImmutable("+{$ttlMinutes} minutes"))->format('Y-m-d H:i:s');

        $stmt->execute([
            'admin_id' => $adminId,
            'token' => $sessionToken,
            'ip' => $ip ? @inet_pton($ip) : null,
            'agent' => substr($userAgent ?? '', 0, 250),
            'expires_at' => $expires,
        ]);
    }

    public function deleteSession(string $sessionToken): void
    {
        $stmt = $this->db->prepare('DELETE FROM admin_sessions WHERE session_token = :token');
        $stmt->execute(['token' => $sessionToken]);
    }
}
