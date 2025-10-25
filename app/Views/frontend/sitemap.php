<?php
/** @var int $totalAgents */
/** @var int $totalPosts */
/** @var string $sitemapUrl */
/** @var string $mainSite */

$mainSite = $mainSite ?? 'https://airewardrop.xyz';
?>
<section class="ag-section">
    <h1 class="ag-title">Sitemap & Indexing</h1>
    <p class="ag-lead">Search engines can stay in sync via our XML sitemap, updated automatically after each publish.</p>

    <div class="ag-status-panel">
        <div>
            <h2>Coverage</h2>
            <p class="ag-status-panel__text">
                Agents indexed: <strong><?= (int)$totalAgents; ?></strong><br>
                Published posts: <strong><?= (int)$totalPosts; ?></strong>
            </p>
        </div>
        <div>
            <h2>XML endpoint</h2>
            <p class="ag-status-panel__text">
                <a href="<?= htmlspecialchars($sitemapUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener"><?= htmlspecialchars($sitemapUrl, ENT_QUOTES, 'UTF-8'); ?></a>
            </p>
        </div>
    </div>

    <div class="ag-status-panel">
        <div>
            <h2>Usage tips</h2>
            <ol style="margin:0;padding-left:18px;color:rgba(246,247,255,0.78);">
                <li>Submit the XML URL inside Google Search Console / Bing Webmaster.</li>
                <li>Configure crawlers to fetch hourly for faster indexation of new signals.</li>
                <li>Use the JSON ingest API to publish instantly, then rely on the sitemap for discovery.</li>
            </ol>
        </div>
        <div>
            <h2>Main site</h2>
            <p class="ag-status-panel__text">
                Partner content lives on <a href="<?= htmlspecialchars($mainSite, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">AIRewardrop.xyz</a>.
            </p>
        </div>
    </div>
</section>
