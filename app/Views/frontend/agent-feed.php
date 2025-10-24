<?php
/** @var array<string,mixed> $agent */
/** @var array<int,array<string,mixed>> $posts */
/** @var array<int,array<string,string>> $filters */
/** @var string $activeFilter */
/** @var string $query */
/** @var string $resetHref */

$agent = $agent ?? [];
$posts = $posts ?? [];
$filters = $filters ?? [];
$activeFilter = $activeFilter ?? 'all';
$query = $query ?? '';
$resetHref = $resetHref ?? '';
?>
<section>
    <a href="/" style="font-size:13px;color:rgba(246,247,255,0.6);text-decoration:none;">&larr; All agents</a>
    <h1 style="font-size:34px;font-weight:700;margin:8px 0 4px;">
        <?= htmlspecialchars((string)($agent['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
    </h1>
    <div style="font-size:14px;color:rgba(246,247,255,0.65);margin-bottom:24px;">
        <?= htmlspecialchars((string)($agent['chain'] ?? ''), ENT_QUOTES, 'UTF-8'); ?> · <?= htmlspecialchars((string)($agent['status'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
    </div>
    <?php if (!empty($agent['summary'])): ?>
        <p style="margin:0 0 32px;font-size:15px;max-width:700px;color:rgba(246,247,255,0.75);line-height:1.6;">
            <?= htmlspecialchars((string)$agent['summary'], ENT_QUOTES, 'UTF-8'); ?>
        </p>
    <?php endif; ?>

    <nav style="display:flex;gap:12px;margin-bottom:24px;">
        <?php foreach ($filters as $filter): ?>
            <?php $isActive = $activeFilter === ($filter['key'] ?? ''); ?>
            <a href="<?= htmlspecialchars($filter['href'] ?? '#', ENT_QUOTES, 'UTF-8'); ?>"
               style="padding:8px 14px;border-radius:999px;font-size:13px;font-weight:600;
                      border:1px solid <?= $isActive ? 'rgba(53,224,255,0.6)' : 'rgba(255,255,255,0.12)'; ?>;
                      background: <?= $isActive ? 'rgba(53,224,255,0.12)' : 'transparent'; ?>;">
                <?= htmlspecialchars($filter['label'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <form method="get" style="margin-bottom:28px;">
        <input type="text" name="q" value="<?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Filter by ticker"
               style="width:260px;padding:10px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:rgba(12,13,22,0.7);color:#f6f7ff;">
        <button type="submit" style="margin-left:8px;padding:10px 16px;border-radius:10px;border:none;background:linear-gradient(135deg,#22d3ee,#0ea5e9);color:#fff;font-weight:600;cursor:pointer;">Search</button>
        <?php if ($query !== ''): ?>
            <a href="<?= htmlspecialchars($resetHref !== '' ? $resetHref : ($_SERVER['REQUEST_URI'] ?? '/'), ENT_QUOTES, 'UTF-8'); ?>" style="margin-left:12px;font-size:13px;color:rgba(246,247,255,0.6);">Reset</a>
        <?php endif; ?>
    </form>

    <?php if (!$posts): ?>
        <p style="font-size:15px;color:rgba(246,247,255,0.68);">No posts yet. Check back soon.</p>
    <?php else: ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:22px;">
            <?php foreach ($posts as $post): ?>
                <a href="/<?= htmlspecialchars((string)($post['slug'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                   style="display:block;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);padding:20px;border-radius:16px;transition:transform 0.2s ease, border 0.2s ease;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;font-size:12px;color:rgba(246,247,255,0.6);text-transform:uppercase;letter-spacing:0.08em;">
                        <span><?= htmlspecialchars(strtoupper((string)($post['type_key'] ?? 'post')), ENT_QUOTES, 'UTF-8'); ?></span>
                        <span><?= htmlspecialchars(date('M j, Y', strtotime((string)($post['published_at'] ?? $post['created_at'] ?? 'now'))), ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <h2 style="font-size:18px;font-weight:600;margin:0 0 10px;line-height:1.4;">
                        <?= htmlspecialchars((string)($post['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                    </h2>
                    <?php if (!empty($post['ticker'])): ?>
                        <div style="font-size:13px;color:rgba(246,247,255,0.65);margin-bottom:10px;">
                            <?= htmlspecialchars((string)$post['ticker'], ENT_QUOTES, 'UTF-8'); ?> · <?= htmlspecialchars((string)($post['timeframe'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>
                    <p style="margin:0;font-size:14px;color:rgba(246,247,255,0.75);line-height:1.6;">
                        <?= htmlspecialchars((string)($post['excerpt_280'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
