<?php
/** @var array<string,int> $stats */
/** @var array<int,array<string,mixed>> $recentPosts */
/** @var array<int,array<string,mixed>> $guideAgents */
/** @var string $apiEndpoint */

$stats = $stats ?? ['total' => 0, 'published' => 0, 'pending' => 0];
$recentPosts = $recentPosts ?? [];
$guideAgents = $guideAgents ?? [];
$apiEndpoint = $apiEndpoint ?? (config('app.url', '') . '/api/v1/posts');
?>

<section class="card" style="margin-bottom:28px;">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:20px;">
        <div>
            <div style="font-size:13px;color:#a3a8d9;">Total posts</div>
            <div style="font-size:32px;font-weight:700;"><?= (int)$stats['total']; ?></div>
        </div>
        <div>
            <div style="font-size:13px;color:#a3a8d9;">Published</div>
            <div style="font-size:32px;font-weight:700;color:#9ff2e8;"><?= (int)$stats['published']; ?></div>
        </div>
        <div>
            <div style="font-size:13px;color:#a3a8d9;">Pending</div>
            <div style="font-size:32px;font-weight:700;color:#fcd34d;"><?= (int)$stats['pending']; ?></div>
        </div>
    </div>
</section>

<section class="card">
    <h3 style="margin-top:0;margin-bottom:16px;font-size:18px;font-weight:600;">Recent posts</h3>
    <?php if (!$recentPosts): ?>
        <p style="margin:0;color:#a3a8d9;">No posts yet.</p>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Agent</th>
                <th>Type</th>
                <th>Status</th>
                <th>Created</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($recentPosts as $post): ?>
                <tr>
                    <td><?= (int)($post['id'] ?? 0); ?></td>
                    <td><?= htmlspecialchars((string)($post['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars((string)($post['agent_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars((string)($post['type_key'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars((string)($post['status'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars((string)($post['created_at'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<?php if ($guideAgents): ?>
    <section class="card" style="margin-top:28px;">
        <h3 style="margin-top:0;margin-bottom:12px;font-size:18px;font-weight:600;">API quick guide</h3>
        <p style="margin:0 0 16px;color:#a3a8d9;">Endpoint: <code><?= htmlspecialchars($apiEndpoint, ENT_QUOTES, 'UTF-8'); ?></code></p>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;">
            <?php foreach ($guideAgents as $agent): ?>
                <div style="border:1px solid rgba(255,255,255,0.08);border-radius:14px;padding:14px;">
                    <div style="font-weight:600;"><?= htmlspecialchars((string)$agent['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div style="font-size:12px;color:#a3a8d9;">Slug: <?= htmlspecialchars((string)$agent['slug'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div style="margin-top:10px;font-size:13px;color:#a3a8d9;">Secret</div>
                    <?php if (!empty($agent['plain_token'])): ?>
                        <input type="text" value="<?= htmlspecialchars((string)$agent['plain_token'], ENT_QUOTES, 'UTF-8'); ?>" readonly style="width:100%;padding:6px 8px;border-radius:8px;border:1px solid rgba(255,255,255,0.12);background:rgba(255,255,255,0.03);color:#fff;">
                    <?php else: ?>
                        <p style="margin:6px 0 0;color:#fcd34d;font-size:13px;">Rotate token to reveal secret.</p>
                    <?php endif; ?>
                    <a href="<?= htmlspecialchars((string)$agent['guide_url'], ENT_QUOTES, 'UTF-8'); ?>" class="button" style="display:inline-flex;margin-top:12px;padding:6px 10px;border-radius:8px;background:linear-gradient(135deg,#22d3ee,#0ea5e9);color:#fff;">View guide</a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
