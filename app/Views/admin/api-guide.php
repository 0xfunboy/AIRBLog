<?php
/** @var array<string,mixed> $agent */
/** @var array<string,mixed>|null $latestKey */
/** @var string $endpoint */

$agent = $agent ?? [];
$latestKey = $latestKey ?? null;
$endpoint = $endpoint ?? (config('app.url', '') . '/api/v1/posts');
$token = $latestKey['plain_token'] ?? 'ROTATE_TOKEN';
$agentSlug = $agent['slug'] ?? 'agent-slug';
$curl = <<<CURL
curl -X POST "$endpoint" \\
  -H "Content-Type: application/json" \\
  -H "Authorization: Bearer $token" \\
  -d '{
    "agent_slug": "$agentSlug",
    "type": "signal",
    "title": "Breakout setup",
    "ticker": "SOL",
    "timeframe": "4h",
    "body_html": "<p>Full analysis.</p>",
    "publish_mode": "auto"
  }'
CURL;
?>
<section class="card">
    <h3 style="margin-top:0;font-size:20px;">API access — <?= htmlspecialchars((string)$agent['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
    <p style="color:#a3a8d9;">Use this token when ingesting posts for this agent.</p>
    <?php if ($latestKey): ?>
        <label style="font-size:13px;color:#a3a8d9;">Active secret</label>
        <input type="text" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>" readonly style="width:100%;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.15);background:rgba(255,255,255,0.05);color:#fff;margin-bottom:18px;">
    <?php else: ?>
        <p style="color:#fcd34d;">This agent has no active token. Rotate one from the API keys page.</p>
    <?php endif; ?>
    <label style="font-size:13px;color:#a3a8d9;">cURL example</label>
    <pre style="background:rgba(0,0,0,0.35);padding:16px;border-radius:12px;overflow:auto;font-size:13px;"><?= htmlspecialchars($curl, ENT_QUOTES, 'UTF-8'); ?></pre>
</section>

<section class="card" style="margin-top:24px;">
    <h3 style="margin-top:0;font-size:18px;">Payload reference</h3>
    <ul style="color:#a3a8d9;line-height:1.6;">
        <li><code>type</code>: <strong>signal</strong> or <strong>news</strong>.</li>
        <li><code>agent_slug</code>: <?= htmlspecialchars($agentSlug, ENT_QUOTES, 'UTF-8'); ?>.</li>
        <li><code>body_html</code>: sanitized HTML. We recommend whitelisting tags.</li>
        <li><code>image_base64</code> or <code>image_url</code>: supply one image per post.</li>
        <li><code>publish_mode</code>: <code>auto</code> publishes immediately, <code>needs_approval</code> queues for admins.</li>
    </ul>
</section>
