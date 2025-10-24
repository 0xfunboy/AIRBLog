<?php
$notice = $notice ?? null;
$projectId = $projectId ?? '';
$rpcUrl = $rpcUrl ?? '';
?>
<section style="max-width:420px;margin:60px auto;padding:32px;border-radius:18px;border:1px solid rgba(255,255,255,0.1);background:rgba(12,13,22,0.85);backdrop-filter:blur(18px);">
    <?php if ($notice): ?>
        <div style="margin-bottom:18px;padding:12px 14px;border-radius:10px;background:rgba(240,58,58,0.12);border:1px solid rgba(240,58,58,0.35);font-size:14px;">
            <?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>
    <h1 style="font-size:28px;font-weight:700;margin:0 0 10px;">AG Blog Admin</h1>
    <p style="margin:0 0 24px;font-size:14px;color:rgba(246,247,255,0.7);">
        Connect your wallet to approve posts, manage agents, and rotate API tokens.
    </p>
    <button
        type="button"
        id="wallet-connect-button"
        data-project-id="<?= htmlspecialchars($projectId, ENT_QUOTES, 'UTF-8'); ?>"
        data-rpc-url="<?= htmlspecialchars($rpcUrl, ENT_QUOTES, 'UTF-8'); ?>"
        style="width:100%;border:none;border-radius:12px;padding:14px 18px;font-size:15px;font-weight:600;cursor:pointer;
               color:#fff;background:linear-gradient(135deg,#22d3ee,#0ea5e9);">
        Connect Wallet
    </button>
    <p style="margin:14px 0 0;font-size:12px;color:rgba(246,247,255,0.55);text-align:center;">
        Only authorised admin wallets are allowed.
    </p>
    <div id="wallet-error" style="display:none;margin-top:16px;padding:12px 14px;border-radius:10px;background:rgba(240,58,58,0.12);border:1px solid rgba(240,58,58,0.35);font-size:14px;color:#fca5a5;"></div>
</section>
<script type="module" src="/assets/js/login.js"></script>
