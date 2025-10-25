<?php
declare(strict_types=1);

namespace App\Support;

use App\Core\Database;
use PDO;

final class SeedImporter
{
    private static bool $executed = false;

    public static function ensureSeeded(): void
    {
        if (self::$executed) {
            return;
        }

        self::$executed = true;

        $seedPath = BASE_PATH . '/database/seed-data.php';
        if (!is_file($seedPath)) {
            return;
        }

        $seed = require $seedPath;
        if (!is_array($seed)) {
            return;
        }

        $pdo = Database::connection();

        self::seedAdmins($pdo, $seed['admins'] ?? [], (array)(config('wallet.allowed_addresses', []) ?? []));
        self::seedAgents($pdo, $seed['agents'] ?? []);
        self::seedPostTypes($pdo, $seed['agent_post_types'] ?? []);
    }

    public static function seedAdminsFromAllowedAddresses(): void
    {
        $pdo = Database::connection();
        self::seedAdmins($pdo, [], (array)(config('wallet.allowed_addresses', []) ?? []));
    }

    private static function seedAdmins(PDO $pdo, array $admins, array $allowedAddresses): void
    {
        $records = [];

        foreach ($admins as $admin) {
            if (empty($admin['wallet_address'])) {
                continue;
            }

            $records[] = [
                'display_name' => $admin['display_name'] ?? 'Admin',
                'wallet_address' => strtolower((string)$admin['wallet_address']),
                'email' => $admin['email'] ?? null,
            ];
        }

        foreach ($allowedAddresses as $address) {
            $address = strtolower(trim((string)$address));
            if ($address === '') {
                continue;
            }

            $records[] = [
                'display_name' => 'Admin',
                'wallet_address' => $address,
                'email' => null,
            ];
        }

        if (!$records) {
            return;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO admins (display_name, wallet_address, email)
             VALUES (:display_name, :wallet_address, :email)
             ON DUPLICATE KEY UPDATE
                display_name = VALUES(display_name),
                email = VALUES(email),
                updated_at = CURRENT_TIMESTAMP'
        );

        foreach ($records as $record) {
            $stmt->execute($record);
        }
    }

    private static function seedAgents(PDO $pdo, array $agents): void
    {
        if (!$agents) {
            return;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO agents (name, slug, chain, status, is_visible, summary, site_url, image_url, badge, featured_order)
             VALUES (:name, :slug, :chain, :status, :is_visible, :summary, :site_url, :image_url, :badge, :featured_order)
             ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                chain = VALUES(chain),
                status = VALUES(status),
                is_visible = VALUES(is_visible),
                summary = VALUES(summary),
                site_url = VALUES(site_url),
                image_url = VALUES(image_url),
                badge = VALUES(badge),
                featured_order = VALUES(featured_order),
                updated_at = CURRENT_TIMESTAMP'
        );

        foreach ($agents as $agent) {
            $stmt->execute([
                'name' => $agent['name'] ?? '',
                'slug' => $agent['slug'] ?? self::slugify($agent['name'] ?? ''),
                'chain' => $agent['chain'] ?? '',
                'status' => $agent['status'] ?? 'Live',
                'is_visible' => (int)($agent['is_visible'] ?? 1),
                'summary' => $agent['summary'] ?? null,
                'site_url' => $agent['site_url'] ?? '',
                'image_url' => $agent['image_url'] ?? '',
                'badge' => $agent['badge'] ?? null,
                'featured_order' => $agent['featured_order'] ?? 0,
            ]);
        }
    }

    private static function seedPostTypes(PDO $pdo, array $postTypes): void
    {
        if (!$postTypes) {
            return;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO agent_post_types (`key`, label)
             VALUES (:key, :label)
             ON DUPLICATE KEY UPDATE label = VALUES(label)'
        );

        foreach ($postTypes as $postType) {
            if (empty($postType['key']) || empty($postType['label'])) {
                continue;
            }

            $stmt->execute([
                'key' => $postType['key'],
                'label' => $postType['label'],
            ]);
        }
    }

    private static function slugify(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/i', '-', $value);
        $value = trim((string)$value, '-');
        return $value !== '' ? $value : bin2hex(random_bytes(4));
    }
}
