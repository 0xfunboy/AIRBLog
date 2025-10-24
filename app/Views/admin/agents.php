<?php
/** @var array<int,array<string,mixed>> $agents */
/** @var array<int,string> $statuses */

$agents = $agents ?? [];
$statuses = $statuses ?? ['Live', 'In Development'];
?>

<section class="card" style="margin-bottom:28px;">
    <h3 style="margin:0 0 16px;font-size:18px;font-weight:600;">Existing agents</h3>
    <?php if (!$agents): ?>
        <p style="margin:0;color:#a3a8d9;">No agents configured yet.</p>
    <?php else: ?>
        <?php foreach ($agents as $agent): ?>
            <form method="post" action="/admin/agents/<?= (int)$agent['id']; ?>" class="card" style="margin-bottom:16px;background:rgba(255,255,255,0.02);">
                <div class="form-row">
                    <div>
                        <label>Name</label>
                        <input type="text" name="name" value="<?= htmlspecialchars((string)$agent['name'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div>
                        <label>Slug</label>
                        <input type="text" name="slug" value="<?= htmlspecialchars((string)$agent['slug'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div>
                        <label>Chain</label>
                        <input type="text" name="chain" value="<?= htmlspecialchars((string)$agent['chain'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div>
                        <label>Status</label>
                        <select name="status">
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>" <?= ((string)$agent['status'] === $status) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div>
                        <label>Site URL</label>
                        <input type="text" name="site_url" value="<?= htmlspecialchars((string)$agent['site_url'], ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div>
                        <label>Image URL</label>
                        <input type="text" name="image_url" value="<?= htmlspecialchars((string)$agent['image_url'], ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>
                <div>
                    <label>Summary</label>
                    <textarea name="summary" rows="3"><?= htmlspecialchars((string)($agent['summary'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
                <div style="display:flex;justify-content:flex-end;margin-top:16px;">
                    <button type="submit" class="button">Save changes</button>
                </div>
            </form>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<section class="card">
    <h3 style="margin:0 0 16px;font-size:18px;font-weight:600;">Add new agent</h3>
    <form method="post" action="/admin/agents">
        <div class="form-row">
            <div>
                <label>Name</label>
                <input type="text" name="name" required>
            </div>
            <div>
                <label>Slug</label>
                <input type="text" name="slug" required>
            </div>
            <div>
                <label>Chain</label>
                <input type="text" name="chain" required>
            </div>
            <div>
                <label>Status</label>
                <select name="status">
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div>
                <label>Site URL</label>
                <input type="text" name="site_url" placeholder="https://">
            </div>
            <div>
                <label>Image URL</label>
                <input type="text" name="image_url" placeholder="/media/...">
            </div>
        </div>
        <div>
            <label>Summary</label>
            <textarea name="summary" rows="3"></textarea>
        </div>
        <div style="display:flex;justify-content:flex-end;margin-top:16px;">
            <button type="submit" class="button">Create agent</button>
        </div>
    </form>
</section>
