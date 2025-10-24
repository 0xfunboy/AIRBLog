<?php
/** @var array<string,int> $stats */
/** @var array<int,array<string,mixed>> $recentPosts */

$stats = $stats ?? ['total' => 0, 'published' => 0, 'pending' => 0];
$recentPosts = $recentPosts ?? [];
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
