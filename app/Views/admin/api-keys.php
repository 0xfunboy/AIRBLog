<?php
/** @var array<int,array<string,mixed>> $agents */
/** @var array<int,array<int,array<string,mixed>>> $keysByAgent */

$agents = $agents ?? [];
$keysByAgent = $keysByAgent ?? [];
?>

<section class="card">
    <?php if (!$agents): ?>
        <p style="margin:0;color:#a3a8d9;">Add agents before managing API keys.</p>
    <?php else: ?>
        <?php foreach ($agents as $agent): ?>
            <?php $agentId = (int)$agent['id']; ?>
            <div class="card" style="background:rgba(255,255,255,0.02);margin-bottom:18px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <div>
                        <strong><?= htmlspecialchars((string)$agent['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                        <small style="color:#a3a8d9;margin-left:8px;">Slug: <?= htmlspecialchars((string)$agent['slug'], ENT_QUOTES, 'UTF-8'); ?></small>
                    </div>
                    <div style="display:flex;gap:10px;">
                        <a href="/admin/api-keys/<?= $agentId; ?>" class="button" style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.2);">Guide</a>
                        <form method="post" action="/admin/api-keys/rotate">
                            <input type="hidden" name="agent_id" value="<?= $agentId; ?>">
                            <button type="submit" class="button">Rotate token</button>
                        </form>
                    </div>
                </div>
                <?php $keys = $keysByAgent[$agentId] ?? []; ?>
                <?php if (!$keys): ?>
                    <p style="margin:0;color:#a3a8d9;">No keys generated yet.</p>
                <?php else: ?>
                    <table>
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Label</th>
                            <th>Secret</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Last Used</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($keys as $key): ?>
                            <tr>
                                <td><?= (int)($key['id'] ?? 0); ?></td>
                                <td><?= htmlspecialchars((string)($key['label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <?php if (!empty($key['plain_token'])): ?>
                                        <code><?= htmlspecialchars((string)$key['plain_token'], ENT_QUOTES, 'UTF-8'); ?></code>
                                    <?php else: ?>
                                        <span style="color:#a3a8d9;">Hidden</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= !empty($key['is_active']) ? 'Active' : 'Inactive'; ?></td>
                                <td><?= htmlspecialchars((string)($key['created_at'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?= htmlspecialchars((string)($key['last_used_at'] ?? '—'), ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</section>
