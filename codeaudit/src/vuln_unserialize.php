<?php
$input = isset($_POST['data']) ? (string) $_POST['data'] : '';
$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = @unserialize($input); // 【漏洞点】对用户可控字符串直接 unserialize，可能触发对象注入
}
?>
<!doctype html>
<html lang="zh-CN">
<head><meta charset="UTF-8"><title>案例：反序列化</title><link rel="stylesheet" href="/assets/common.css"></head>
<body><div class="wrap">
<p><a href="/index.php">← 返回首页</a></p>
<h1>反序列化（对象注入）</h1>
<p class="muted">观察 <code>unserialize</code> 的入参来源，思考如何改为安全的数据格式（如 JSON）。</p>
<form method="post">
<input type="text" style="width:520px" name="data" value="<?php echo htmlspecialchars($input, ENT_QUOTES, 'UTF-8'); ?>">
<button type="submit">反序列化</button>
</form>
<?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
<pre><?php var_dump($result); ?></pre>
<?php endif; ?>
<div class="card">
<h2>提示</h2>
<p class="muted">先提交一个正常序列化字符串，再观察可控字段如何影响反序列化结果。</p>
<h2>可用 PoC 小卡片</h2>
<pre>a:1:{s:4:"name";s:4:"test";}</pre>
</div>
<?php require_once __DIR__ . '/inc_show_source.php'; audit_lab_show_page_source(__FILE__); ?>
</div></body></html>
