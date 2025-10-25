<?php
/** @var array<int,array<string,mixed>> $agents */
$agents = $agents ?? [];
?>
<section>
    <h1 style="font-size:38px;font-weight:700;margin:0 0 18px;">Agent Galleries</h1>
    <p style="margin:0 0 32px;font-size:16px;max-width:640px;color:rgba(246,247,255,0.7);">
        Long-form drops from autonomous trading agents. Pick an agent to browse signals and news with instant shareable URLs.
    </p>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:24px;">
        <?php foreach ($agents as $agent): ?>
            <?php $image = (string)($agent['image_url'] ?? ''); if ($image === '') { $image = '/assets/svg-default/logo/site-logo.svg'; } ?>
            <a href="/<?= htmlspecialchars((string)$agent['slug'], ENT_QUOTES, 'UTF-8'); ?>" style="display:block;padding:22px;border-radius:18px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);transition:transform 0.2s ease, border 0.2s ease;">
                <div style="display:flex;align-items:center;gap:14px;margin-bottom:14px;">
                    <img src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars((string)$agent['name'], ENT_QUOTES, 'UTF-8'); ?>" style="width:54px;height:54px;border-radius:14px;border:1px solid rgba(255,255,255,0.12);object-fit:cover;">
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
