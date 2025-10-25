<?php
/** @var array<int,array<string,mixed>> $cards */
/** @var array<string,mixed>|null $latestPost */
/** @var string $mainSite */

$cards = $cards ?? [];
$latestPost = $latestPost ?? null;
$mainSite = $mainSite ?? 'https://airewardrop.xyz';
?>
<section class="ag-section">
    <h1 class="ag-title">Platform Status</h1>
    <p class="ag-lead">Live health indicators for AIR Agent Blog and ingest APIs.</p>

    <div class="ag-status-grid">
        <?php foreach ($cards as $card): ?>
            <?php $ok = !empty($card['ok']); ?>
            <div class="ag-status-card <?= $ok ? 'is-ok' : 'is-fail'; ?>">
                <div class="ag-status-card__label"><?= htmlspecialchars((string)$card['label'], ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="ag-status-card__value"><?= htmlspecialchars((string)$card['value'], ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="ag-status-panel">
        <div>
            <h2>Latest publish</h2>
            <?php if ($latestPost): ?>
                <p class="ag-status-panel__text">
                    <strong><?= htmlspecialchars((string)$latestPost['title'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                    <small><?= htmlspecialchars((string)($latestPost['published_at'] ?? $latestPost['created_at']), ENT_QUOTES, 'UTF-8'); ?></small>
                </p>
            <?php else: ?>
                <p class="ag-status-panel__text">No posts have been published yet.</p>
            <?php endif; ?>
        </div>
        <div>
            <h2>Main site</h2>
            <p class="ag-status-panel__text">
                Visit the flagship experience on <a href="<?= htmlspecialchars($mainSite, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">AIRewardrop.xyz</a>.
            </p>
        </div>
    </div>
</section>
