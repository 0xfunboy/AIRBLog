<?php
declare(strict_types=1);

$basePath = dirname(__DIR__);
$lockFile = $basePath . '/storage/install.lock';
$schemaFile = $basePath . '/database/schema.sql';

require $basePath . '/app/bootstrap.php';

use App\Core\Database;
use App\Support\SeedImporter;

$alreadyInstalled = file_exists($lockFile);
$messages = [];
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$alreadyInstalled) {
    try {
        $pdo = Database::connection();
        $schema = file_get_contents($schemaFile);
        if ($schema === false) {
            throw new RuntimeException('Schema file not found.');
        }

        $pdo->exec($schema);
        SeedImporter::ensureSeeded();

        if (!is_dir(dirname($lockFile)) && !mkdir(dirname($lockFile), 0775, true) && !is_dir(dirname($lockFile))) {
            throw new RuntimeException('Unable to prepare storage directory.');
        }

        file_put_contents($lockFile, (string)time());
        $alreadyInstalled = true;
        $messages[] = 'Database schema installed successfully.';
        $messages[] = 'Seed data imported (agents, post types, admin wallets).';
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>AG Blog Installer</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            color-scheme: dark;
            font-family: "Inter", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #05060c;
            color: #f3f4ff;
        }
        .card {
            width: min(520px, 100%);
            padding: 32px;
            border-radius: 18px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(12, 13, 26, 0.9);
            box-shadow: 0 20px 60px rgba(0,0,0,0.55);
        }
        h1 {
            margin: 0 0 12px;
            font-size: 28px;
        }
        p {
            margin: 0 0 18px;
            color: rgba(243, 244, 255, 0.72);
        }
        .flash {
            border-radius: 12px;
            padding: 12px 14px;
            font-size: 14px;
            margin-bottom: 12px;
        }
        .flash.error {
            background: rgba(240, 58, 58, 0.12);
            border: 1px solid rgba(240, 58, 58, 0.35);
            color: #fda4af;
        }
        .flash.success {
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.35);
            color: #a7f3d0;
        }
        button {
            width: 100%;
            border: none;
            border-radius: 12px;
            padding: 14px;
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            background: linear-gradient(135deg, #22d3ee, #0ea5e9);
        }
        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        code {
            font-family: "Fira Code", monospace;
            font-size: 13px;
            background: rgba(255,255,255,0.08);
            padding: 2px 6px;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>AG Blog Installer</h1>
        <?php if ($error): ?>
            <div class="flash error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <?php foreach ($messages as $message): ?>
            <div class="flash success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endforeach; ?>

        <?php if ($alreadyInstalled && !$error): ?>
            <p>The application is already installed. To reinstall, delete <code>storage/install.lock</code> and reload this page.</p>
        <?php else: ?>
            <p>This will run <code>database/schema.sql</code> and seed initial data (agents, post types, admin wallets). Ensure <code>.env.php</code> is configured with a valid database before continuing.</p>
            <form method="post">
                <button type="submit">Install Now</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
