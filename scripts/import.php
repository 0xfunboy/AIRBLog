<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Container;
use App\Core\Database;

$pdo = Database::connection();
$pdo->exec('SET NAMES utf8mb4');

resetTables($pdo, [
    'agent_posts',
    'agent_api_keys',
    'agent_post_types',
    'agents',
    'admin_sessions',
    'admin_nonces',
    'admins',
]);

seedAdmins($pdo, Container::get('config', []));

$seedFile = dirname(__DIR__) . '/database/seed-data.php';
if (is_file($seedFile)) {
    $seed = require $seedFile;
    seedAgents($pdo, $seed['agents'] ?? []);
    seedPostTypes($pdo, $seed['agent_post_types'] ?? []);
}

echo "Seed import completed.\n";

function resetTables(PDO $pdo, array $tables): void
{
    $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
    foreach ($tables as $table) {
        $pdo->exec("TRUNCATE TABLE {$table}");
    }
    $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
}

function seedAdmins(PDO $pdo, array $config): void
{
    $allowed = $config['wallet']['allowed_addresses'] ?? [];
    if (!is_array($allowed) || !$allowed) {
        echo "No admin wallets configured; skipping admin seed.\n";
        return;
    }

    $insert = $pdo->prepare(
        'INSERT INTO admins (display_name, wallet_address) VALUES (:display_name, :wallet_address)
         ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP'
    );

    foreach ($allowed as $address) {
        $address = strtolower((string)$address);
        if ($address === '') {
            continue;
        }

        $insert->execute([
            'display_name' => 'Admin',
            'wallet_address' => $address,
        ]);
        echo "Admin wallet seeded: {$address}\n";
    }
}

function seedAgents(PDO $pdo, array $agents): void
{
    if (!$agents) {
        echo "No agents provided in seed data.\n";
        return;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO agents (name, slug, chain, status, is_visible, summary, site_url, image_url, badge, featured_order)
         VALUES (:name, :slug, :chain, :status, :is_visible, :summary, :site_url, :image_url, :badge, :featured_order)
         ON DUPLICATE KEY UPDATE
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
            'slug' => $agent['slug'] ?? slugify($agent['name'] ?? ''),
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

    echo "Agents seeded: " . count($agents) . "\n";
}

function seedPostTypes(PDO $pdo, array $postTypes): void
{
    if (!$postTypes) {
        echo "No post types provided in seed data.\n";
        return;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO agent_post_types (`key`, label) VALUES (:key, :label)
         ON DUPLICATE KEY UPDATE label = VALUES(label)'
    );

    foreach ($postTypes as $postType) {
        $stmt->execute([
            'key' => $postType['key'] ?? '',
            'label' => $postType['label'] ?? '',
        ]);
    }

    echo "Agent post types seeded: " . count($postTypes) . "\n";
}

function slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/i', '-', $value);
    $value = trim((string)$value, '-');
    return $value !== '' ? $value : bin2hex(random_bytes(4));
}
