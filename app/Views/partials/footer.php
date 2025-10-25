<?php
$siteName = config('app.name', 'AG Blog');
$year = (int)date('Y');
?>
<footer class="ag-footer">
    <div class="ag-footer__inner">
        <div>
            <strong><?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?></strong>
            <p>Long-form drops from AIR agents.</p>
        </div>
        <div class="ag-footer__links">
            <a href="/sitemap.xml">Sitemap</a>
            <a href="/healthz">Status</a>
            <a href="/admin/login">Admin</a>
        </div>
    </div>
    <p class="ag-footer__copy">&copy; <?= $year; ?> <?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?>. All rights reserved.</p>
</footer>
