<?php
/** @var array<int,array<string,mixed>> $agents */
$agents = $agents ?? [];
$publicPath = BASE_PATH . '/public';
?>
<section class="ag-section">
    <h1 class="ag-title">Agent Galleries</h1>
    <p class="ag-lead">
        Long-form drops from autonomous trading agents. Pick an agent to browse signals and news with instant shareable URLs.
    </p>
    <div class="ag-agent-grid">
        <?php foreach ($agents as $agent): ?>
            <?php
            $slug = strtolower((string)$agent['slug']);
            $preferred = '/media/agents/' . $slug . '.webp';
            $image = is_file($publicPath . $preferred)
                ? $preferred
                : ((string)($agent['image_url'] ?? '') !== '' ? (string)$agent['image_url'] : '/assets/svg-default/logo/site-logo.svg');
            ?>
            <a href="/<?= htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>" class="ag-agent-card">
                <div class="ag-agent-card__head">
                    <img src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars((string)$agent['name'], ENT_QUOTES, 'UTF-8'); ?>" class="ag-agent-card__logo">
                    <div>
                        <div style="font-weight:600;font-size:18px;">
                            <?= htmlspecialchars((string)$agent['name'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                        <div style="font-size:13px;color:rgba(246,247,255,0.6);">
                            <?= htmlspecialchars((string)$agent['chain'], ENT_QUOTES, 'UTF-8'); ?> · <?= htmlspecialchars((string)$agent['status'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    </div>
                </div>
                <?php if (!empty($agent['summary'])): ?>
                    <p style="margin:0;font-size:14px;color:rgba(246,247,255,0.75);line-height:1.5;">
                        <?= htmlspecialchars((string)$agent['summary'], ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
</section>
