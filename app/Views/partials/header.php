<?php
$siteName = config('app.name', 'AIR Agent Blog');
$siteUrl = rtrim((string)config('app.url', ''), '/');
$logo = '/assets/svg-default/logo/site-logo.svg';
$mainSite = 'https://airewardrop.xyz';
?>
<header class="ag-header">
    <div class="ag-header__inner">
        <a class="ag-brand" href="/">
            <img src="<?= htmlspecialchars($logo, ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?>" class="ag-brand__logo">
            <span class="ag-brand__text"><?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?></span>
        </a>
        <nav class="ag-nav">
            <a href="/">Home</a>
            <a href="/status">Status</a>
            <a href="/sitemap-info">Sitemap</a>
            <a href="/admin/login">Admin</a>
        </nav>
        <div class="ag-header__actions">
            <a class="ag-cta ag-cta--ghost" href="<?= htmlspecialchars($mainSite, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">AIRewardrop</a>
            <a class="ag-cta" href="<?= htmlspecialchars($siteUrl !== '' ? $siteUrl . '/admin/login' : '/admin/login', ENT_QUOTES, 'UTF-8'); ?>">Wallet Login</a>
        </div>
    </div>
</header>
