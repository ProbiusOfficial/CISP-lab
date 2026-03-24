<?php
session_start();
if (!isset($_SESSION['email'])) {
    $_SESSION['email'] = 'student@example.com';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['email'] = (string)($_POST['email'] ?? $_SESSION['email']); // 【漏洞点】敏感修改无 CSRF token
}
?>
<!doctype html>
<html lang="zh-CN"><head><meta charset="UTF-8"><title>案例：CSRF</title><link rel="stylesheet" href="/assets/common.css"></head>
<body><div class="wrap">
<p><a href="/index.php">← 返回首页</a></p>
<h1>资料修改（无 CSRF 防护）</h1>
<p>当前邮箱：<code><?php echo htmlspecialchars($_SESSION['email'], ENT_QUOTES, 'UTF-8'); ?></code></p>
<form method="post">
<input type="text" name="email" value="<?php echo htmlspecialchars($_SESSION['email'], ENT_QUOTES, 'UTF-8'); ?>">
<button type="submit">保存</button>
</form>
<div class="card">
<h2>提示</h2>
<p class="muted">观察表单里是否有随机 token；没有的话，第三方页面可伪造同源用户请求。</p>
<h2>可用 PoC 小卡片</h2>
<pre>&lt;form action="/vuln_csrf.php" method="POST"&gt;
  &lt;input name="email" value="attacker@example.com"&gt;
&lt;/form&gt;</pre>
</div>
<?php require_once __DIR__ . '/inc_show_source.php'; audit_lab_show_page_source(__FILE__); ?>
</div></body></html>
