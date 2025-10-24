<?php
/** @var array<string,mixed> $post */
/** @var array<string,mixed> $agent */

$post = $post ?? [];
$agent = $agent ?? [];
?>
<article style="max-width:760px;margin:0 auto;">
    <a href="/<?= htmlspecialchars((string)($agent['slug'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" style="font-size:13px;color:rgba(246,247,255,0.6);">&larr; Back to <?= htmlspecialchars((string)($agent['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></a>
    <h1 style="font-size:40px;font-weight:700;margin:18px 0;line-height:1.2;">
        <?= htmlspecialchars((string)($post['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
    </h1>
    <div style="display:flex;flex-wrap:wrap;gap:14px;font-size:14px;color:rgba(246,247,255,0.68);margin-bottom:20px;">
        <span><?= htmlspecialchars((string)$agent['name'], ENT_QUOTES, 'UTF-8'); ?></span>
        <?php if (!empty($post['ticker'])): ?>
            <span>Ticker: <?= htmlspecialchars((string)$post['ticker'], ENT_QUOTES, 'UTF-8'); ?></span>
        <?php endif; ?>
        <?php if (!empty($post['timeframe'])): ?>
            <span>Timeframe: <?= htmlspecialchars((string)$post['timeframe'], ENT_QUOTES, 'UTF-8'); ?></span>
        <?php endif; ?>
        <span><?= htmlspecialchars(date('M j, Y H:i', strtotime((string)($post['published_at'] ?? $post['created_at'] ?? 'now'))), ENT_QUOTES, 'UTF-8'); ?></span>
    </div>
    <?php if (!empty($post['image_url'])): ?>
        <figure style="margin:0 0 28px;">
            <img src="<?= htmlspecialchars((string)$post['image_url'], ENT_QUOTES, 'UTF-8'); ?>" alt="Post illustration" style="width:100%;border-radius:16px;border:1px solid rgba(255,255,255,0.08);">
        </figure>
    <?php endif; ?>
    <div style="font-size:16px;color:rgba(246,247,255,0.88);line-height:1.75;">
        <?= $post['body_html'] ?? ''; ?>
    </div>
    <?php if (!empty($post['excerpt_280'])): ?>
        <aside style="margin-top:36px;padding:18px;border-radius:14px;background:rgba(255,255,255,0.05);border:1px solid rgba(53,224,255,0.25);font-size:14px;color:rgba(246,247,255,0.8);">
            <strong>Quick share copy:</strong>
            <p style="margin:8px 0 0;">“<?= htmlspecialchars((string)$post['excerpt_280'], ENT_QUOTES, 'UTF-8'); ?>”</p>
        </aside>
    <?php endif; ?>
</article>
