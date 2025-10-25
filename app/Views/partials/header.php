<?php
$siteName = config('app.name', 'AG Blog');
$siteUrl = rtrim((string)config('app.url', ''), '/');
$logo = '/assets/svg-default/logo/site-logo.svg';
?>
<header class="ag-header">
    <div class="ag-header__inner">
        <a class="ag-brand" href="/">
            <img src="<?= htmlspecialchars($logo, ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?>" class="ag-brand__logo">
            <span class="ag-brand__text"><?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?></span>
        </a>
        <nav class="ag-nav">
            <a href="/">Home</a>
            <a href="/admin/login">Dashboard</a>
            <a href="/healthz">Status</a>
        </nav>
        <a class="ag-cta" href="<?= htmlspecialchars($siteUrl !== '' ? $siteUrl . '/admin/login' : '/admin/login', ENT_QUOTES, 'UTF-8'); ?>">Wallet Login</a>
    </div>
</header>
