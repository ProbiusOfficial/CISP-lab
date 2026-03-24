<?php
$user = isset($_GET['user']) ? (string) $_GET['user'] : 'guest';
$isAdmin = isset($_GET['is_admin']) && $_GET['is_admin'] === '1'; // 【漏洞点】仅信任前端/URL 参数决定权限
?>
<!doctype html>
<html lang="zh-CN"><head><meta charset="UTF-8"><title>案例：越权</title><link rel="stylesheet" href="/assets/common.css"></head>
<body><div class="wrap">
<p><a href="/index.php">← 返回首页</a></p>
<h1>后台入口（鉴权薄弱）</h1>
<p>当前用户：<code><?php echo htmlspecialchars($user, ENT_QUOTES, 'UTF-8'); ?></code></p>
<?php if ($isAdmin): ?>
<div class="card"><strong>管理员面板</strong><p>这里本应仅后端会话判定后可见。</p></div>
<?php else: ?>
<p class="warn">你不是管理员。</p>
<?php endif; ?>
<div class="card">
<h2>提示</h2>
<p class="muted">看看权限判断是否依赖后端会话，还是仅依赖 URL 参数。</p>
<h2>可用 PoC 小卡片</h2>
<pre>?user=guest&is_admin=1</pre>
</div>
<?php require_once __DIR__ . '/inc_show_source.php'; audit_lab_show_page_source(__FILE__); ?>
</div></body></html>
