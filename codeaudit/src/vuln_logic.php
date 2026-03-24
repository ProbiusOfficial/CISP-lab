<?php
session_start();
if (!isset($_SESSION['balance'])) {
    $_SESSION['balance'] = 100;
}
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (int)($_POST['amount'] ?? 0);
    $_SESSION['balance'] -= $amount; // 【漏洞点】业务逻辑缺陷：未校验 amount 必须 > 0，负数可“反向加钱”
    $msg = '已处理金额：' . $amount;
}
?>
<!doctype html>
<html lang="zh-CN"><head><meta charset="UTF-8"><title>案例：业务逻辑</title><link rel="stylesheet" href="/assets/common.css"></head>
<body><div class="wrap">
<p><a href="/index.php">← 返回首页</a></p>
<h1>余额扣减（逻辑漏洞）</h1>
<p>当前余额：<code><?php echo htmlspecialchars((string)$_SESSION['balance'], ENT_QUOTES, 'UTF-8'); ?></code></p>
<form method="post">
<input type="text" name="amount" value="10"><button type="submit">扣减</button>
</form>
<?php if ($msg !== ''): ?><p class="warn"><?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></p><?php endif; ?>
<div class="card">
<h2>提示</h2>
<p class="muted">关注金额边界：是否限制正数、上限、整数范围，是否允许重复提交。</p>
<h2>可用 PoC 小卡片</h2>
<pre>amount=-100</pre>
</div>
<?php require_once __DIR__ . '/inc_show_source.php'; audit_lab_show_page_source(__FILE__); ?>
</div></body></html>
