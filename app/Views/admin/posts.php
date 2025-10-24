<?php
/** @var array<int,array<string,mixed>> $posts */
/** @var string $status */

$posts = $posts ?? [];
$status = $status ?? 'pending';
$options = [
    'pending' => 'Pending',
    'published' => 'Published',
    'rejected' => 'Rejected',
    'all' => 'All',
];
?>

<section class="card">
    <form method="get" action="/admin/posts" style="margin-bottom:20px;display:flex;gap:12px;align-items:center;">
        <label for="status" style="margin:0;">Status</label>
        <select id="status" name="status" onchange="this.form.submit()" style="max-width:200px;">
            <?php foreach ($options as $value => $label): ?>
                <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>" <?= $status === $value ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if (!$posts): ?>
        <p style="margin:0;color:#a3a8d9;">No posts found for this filter.</p>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Agent</th>
                <th>Type</th>
                <th>Status</th>
                <th>Submitted</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($posts as $post): ?>
                <tr>
                    <td><?= (int)($post['id'] ?? 0); ?></td>
                    <td>
                        <?= htmlspecialchars((string)($post['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?><br>
                        <small style="color:#a3a8d9;"><?= htmlspecialchars((string)($post['slug'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></small>
                    </td>
                    <td><?= htmlspecialchars((string)($post['agent_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars((string)($post['type_key'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars((string)($post['status'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars((string)($post['created_at'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <a class="button" href="<?= htmlspecialchars('/' . ltrim((string)($post['slug'] ?? ''), '/'), ENT_QUOTES, 'UTF-8'); ?>" target="_blank" style="margin-right:6px;">View</a>
                        <?php if (($post['status'] ?? '') === 'pending'): ?>
                            <form class="inline" method="post" action="/admin/posts/<?= (int)$post['id']; ?>/approve" style="margin-right:4px;">
                                <button type="submit" class="button" style="background:linear-gradient(135deg,#22d3ee,#0ea5e9);">Approve</button>
                            </form>
                            <form class="inline" method="post" action="/admin/posts/<?= (int)$post['id']; ?>/reject">
                                <button type="submit" class="button" style="background:linear-gradient(135deg,#f97316,#f43f5e);">Reject</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
