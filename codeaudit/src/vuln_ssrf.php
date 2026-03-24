<?php
$url = isset($_GET['url']) ? (string) $_GET['url'] : '';
$body = '';
$err = '';
if ($url !== '') {
    $body = @file_get_contents($url); // 【漏洞点】服务端直接请求用户给定 URL，可能访问内网或本地协议
    if ($body === false) {
        $err = '请求失败';
    }
}
?>
<!doctype html>
<html lang="zh-CN"><head><meta charset="UTF-8"><title>案例：SSRF</title><link rel="stylesheet" href="/assets/common.css"></head>
<body><div class="wrap">
<p><a href="/index.php">← 返回首页</a></p>
<h1>URL 抓取（SSRF）</h1>
<form method="get">
<input type="text" style="width:520px" name="url" placeholder="http://example.com" value="<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>">
<button type="submit">抓取</button>
</form>
<?php if ($err !== ''): ?><p class="warn"><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></p><?php endif; ?>
<?php if ($body !== ''): ?><pre><?php echo htmlspecialchars(substr($body, 0, 3000), ENT_QUOTES, 'UTF-8'); ?></pre><?php endif; ?>
<div class="card">
<h2>提示</h2>
<p class="muted">先请求公网可访问地址，再尝试本机/内网地址，比较返回差异。</p>
<h2>可用 PoC 小卡片</h2>
<pre>?url=http://127.0.0.1</pre>
</div>
<?php require_once __DIR__ . '/inc_show_source.php'; audit_lab_show_page_source(__FILE__); ?>
</div></body></html>
