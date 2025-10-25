<?php
/** @var string $title */
/** @var string $contentTemplate */
/** @var array $contentData */

use App\Core\Container;
use App\Core\View;

$config = Container::get('config', []);
$siteName = $config['app']['name'] ?? 'AG Blog';
$baseUrl = rtrim((string)($config['app']['url'] ?? ''), '/');

$meta = $contentData['meta'] ?? [];
$description = $meta['description'] ?? 'Long-form trading updates and intelligence from the AG agents.';
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$canonical = $meta['canonical'] ?? ($baseUrl ? $baseUrl . $requestUri : $requestUri);
$shareImage = $meta['image'] ?? ''; 
$pageTitle = $title !== '' ? $title . ' | ' . $siteName : $siteName;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="description" content="<?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:type" content="article">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:description" content="<?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8'); ?>">
    <?php if ($shareImage !== ''): ?>
        <meta property="og:image" content="<?= htmlspecialchars($shareImage, ENT_QUOTES, 'UTF-8'); ?>">
        <meta name="twitter:image" content="<?= htmlspecialchars($shareImage, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
    <script type="module" src="/assets/js/animate.js" defer></script>
    <style>
        :root {
            color-scheme: dark;
        }
        .ag-body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: radial-gradient(circle at top right, rgba(53,224,255,0.12), transparent 50%),
                       radial-gradient(circle at 20% 30%, rgba(240,58,58,0.18), transparent 55%),
                       #05060c;
            color: #f6f7ff;
        }
        .ag-header {
            position: sticky;
            top: 0;
            z-index: 10;
            backdrop-filter: blur(18px);
            background: rgba(5,6,12,0.85);
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .ag-header__inner {
            max-width: 1100px;
            margin: 0 auto;
            padding: 18px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }
        .ag-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            text-decoration: none;
        }
        .ag-brand__logo {
            height: 36px;
            width: auto;
        }
        .ag-nav a {
            margin-left: 18px;
            font-size: 14px;
            color: rgba(246,247,255,0.75);
            text-decoration: none;
        }
        .ag-nav a:hover {
            color: #fff;
        }
        .ag-header__actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }
        .ag-cta {
            padding: 10px 16px;
            border-radius: 999px;
            background: linear-gradient(135deg,#22d3ee,#0ea5e9);
            color: #fff;
            font-weight: 600;
            text-decoration: none;
        }
        .ag-cta--ghost {
            background: transparent;
            border: 1px solid rgba(255,255,255,0.4);
        }
        .ag-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 48px 24px 80px;
        }
        .ag-section {
            margin-bottom: 48px;
        }
        .ag-title {
            font-size: 42px;
            margin: 0 0 12px;
            font-weight: 700;
        }
        .ag-lead {
            margin: 0 0 28px;
            font-size: 17px;
            color: rgba(246,247,255,0.75);
            max-width: 640px;
            line-height: 1.6;
        }
        .ag-agent-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 24px;
        }
        .ag-agent-card {
            display: block;
            padding: 24px;
            border-radius: 20px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            text-decoration: none;
            color: inherit;
            transition: transform 0.25s ease, border 0.25s ease, box-shadow 0.25s ease;
        }
        .ag-agent-card:hover {
            transform: translateY(-4px);
            border-color: rgba(53,224,255,0.6);
            box-shadow: 0 12px 40px rgba(0,0,0,0.35);
        }
        .ag-agent-card__head {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 14px;
        }
        .ag-agent-card__logo {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.14);
            object-fit: cover;
        }
        .ag-status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }
        .ag-status-card {
            border-radius: 16px;
            padding: 18px;
            border: 1px solid rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.03);
        }
        .ag-status-card.is-ok {
            border-color: rgba(16,185,129,0.4);
            background: rgba(16,185,129,0.08);
        }
        .ag-status-card.is-fail {
            border-color: rgba(240,58,58,0.4);
            background: rgba(240,58,58,0.08);
        }
        .ag-status-card__label {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(246,247,255,0.7);
        }
        .ag-status-card__value {
            font-size: 20px;
            font-weight: 600;
            margin-top: 6px;
        }
        .ag-status-panel {
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.03);
            padding: 24px;
            margin-bottom: 24px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
        }
        .ag-status-panel__text {
            margin: 0;
            color: rgba(246,247,255,0.78);
            line-height: 1.6;
        }
        .ag-footer {
            border-top: 1px solid rgba(255,255,255,0.08);
            background: rgba(5,6,12,0.95);
            padding: 32px 24px;
            margin-top: 40px;
        }
        .ag-footer__inner {
            max-width: 1100px;
            margin: 0 auto 16px;
            display: flex;
            justify-content: space-between;
            gap: 24px;
            flex-wrap: wrap;
        }
        .ag-footer__links a {
            margin-left: 16px;
            text-decoration: none;
            color: rgba(246,247,255,0.75);
        }
        .ag-footer__copy {
            text-align: center;
            color: rgba(246,247,255,0.6);
            margin: 0;
        }
    </style>
</head>
<body class="ag-body">
    <?php View::renderPartial('partials/header'); ?>
    <main class="ag-container">
        <?php View::renderPartial($contentTemplate, $contentData ?? []); ?>
    </main>
    <?php View::renderPartial('partials/footer'); ?>
</body>
</html>
