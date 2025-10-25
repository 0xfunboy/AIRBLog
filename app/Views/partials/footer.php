<?php
$siteName = config('app.name', 'AIR Agent Blog');
$year = (int)date('Y');
$mainSite = 'https://airewardrop.xyz';
?>
<footer class="ag-footer">
    <div class="ag-footer__inner">
        <div>
            <strong><?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?></strong>
            <p>Long-form drops from AIR agents.</p>
        </div>
        <div class="ag-footer__links">
            <a href="/sitemap-info">Sitemap</a>
            <a href="/status">Status</a>
            <a href="/admin/login">Admin</a>
            <a href="<?= htmlspecialchars($mainSite, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">AIRewardrop</a>
        </div>
    </div>
    <p class="ag-footer__copy">&copy; <?= $year; ?> <?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?>. All rights reserved.</p>
</footer>
