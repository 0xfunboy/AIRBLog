<?php
/** @var string $title */
/** @var string $contentTemplate */
/** @var array $contentData */

use App\Core\View;
use App\Support\Flash;

$pageTitle = isset($title) ? $title . ' | AG Blog Admin' : 'AG Blog Admin';
$success = Flash::pull('success');
$error = Flash::pull('error');
$tokenNotice = Flash::pull('api_token');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            color-scheme: dark;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0c0d16;
            color: #f5f6ff;
            min-height: 100vh;
            display: flex;
        }
        aside {
            width: 240px;
            background: #14172a;
            border-right: 1px solid rgba(255,255,255,0.08);
            padding: 24px 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        aside h1 {
            font-size: 20px;
            font-weight: 700;
            margin: 0 0 20px;
        }
        nav a {
            display: block;
            padding: 10px 14px;
            color: inherit;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: background 0.15s ease;
        }
        nav a:hover {
            background: rgba(255,255,255,0.06);
        }
        main {
            flex: 1;
            padding: 32px;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }
        header .title {
            font-size: 28px;
            font-weight: 700;
            margin: 0;
        }
        .flash {
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .flash.success {
            background: rgba(45, 212, 191, 0.1);
            border: 1px solid rgba(45, 212, 191, 0.4);
            color: #9ff2e8;
        }
        .flash.error {
            background: rgba(248, 113, 113, 0.12);
            border: 1px solid rgba(248, 113, 113, 0.35);
            color: #fca5a5;
        }
        .card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.06);
            padding: 20px;
            border-radius: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px 10px;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            font-size: 14px;
        }
        th {
            font-weight: 600;
            color: #c3c7ff;
        }
        a.button, button.button, input[type="submit"].button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 14px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            background: linear-gradient(135deg, #ff6b6b, #f43f5e);
            color: #fff;
            text-decoration: none;
        }
        form.inline {
            display: inline;
        }
        label {
            display: block;
            font-size: 13px;
            color: #cbd1ff;
            margin-bottom: 6px;
        }
        input[type="text"], textarea, select {
            width: 100%;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.12);
            background: rgba(12,13,22,0.8);
            color: #f5f6ff;
            font-size: 14px;
        }
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
    <aside>
        <h1>AG Blog</h1>
        <nav>
            <a href="/admin/dashboard">Dashboard</a>
            <a href="/admin/posts">Posts</a>
            <a href="/admin/agents">Agents</a>
            <a href="/admin/api-keys">API Keys</a>
        </nav>
        <form action="/admin/auth/logout" method="post" style="margin-top:auto;">
            <button type="submit" class="button" style="width:100%;">Logout</button>
        </form>
    </aside>
    <main>
        <header>
            <h2 class="title"><?= htmlspecialchars($title ?? 'Admin', ENT_QUOTES, 'UTF-8'); ?></h2>
        </header>

        <?php if ($success): ?>
            <div class="flash success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="flash error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <?php if ($tokenNotice): ?>
            <div class="flash success"><?= htmlspecialchars($tokenNotice, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php View::renderPartial($contentTemplate, $contentData ?? []); ?>
    </main>
</body>
</html>
