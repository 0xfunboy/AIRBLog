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
    <?php endif; ?>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?>">
    <?php if ($shareImage !== ''): ?>
        <meta name="twitter:image" content="<?= htmlspecialchars($shareImage, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>
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
            font-family: 'Inter', system-ui, sans-serif;
            background: radial-gradient(circle at top right, rgba(53,224,255,0.12), transparent 50%),
                       radial-gradient(circle at 20% 30%, rgba(240,58,58,0.18), transparent 55%),
                       #090a12;
            color: #f6f7ff;
            min-height: 100vh;
        }
        a { color: inherit; text-decoration: none; }
        header {
            position: sticky;
            top: 0;
            backdrop-filter: blur(18px);
            background: rgba(9,10,18,0.78);
            border-bottom: 1px solid rgba(255,255,255,0.08);
            z-index: 10;
        }
        .header-inner {
            max-width: 1100px;
            margin: 0 auto;
            padding: 18px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .brand {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.02em;
        }
        nav a {
            margin-left: 18px;
            font-size: 14px;
            font-weight: 500;
            opacity: 0.75;
        }
        nav a:hover { opacity: 1; }
        main {
            max-width: 1100px;
            margin: 0 auto;
            padding: 48px 24px 80px;
        }
        footer {
            border-top: 1px solid rgba(255,255,255,0.08);
            margin-top: 80px;
            padding: 40px 24px;
            text-align: center;
            font-size: 13px;
            color: rgba(246,247,255,0.68);
        }
    </style>
</head>
<body>
<header>
    <div class="header-inner">
        <a class="brand" href="/">AG Blog</a>
        <nav>
            <a href="/">Agents</a>
            <a href="/sitemap.xml">Sitemap</a>
        </nav>
    </div>
</header>
<main>
    <?php View::renderPartial($contentTemplate, $contentData ?? []); ?>
</main>
<footer>
    &copy; <?= date('Y'); ?> <?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?>. Crafted for agent long-form drops.
</footer>
</body>
</html>
