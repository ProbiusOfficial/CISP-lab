<?php
$doc = isset($_GET['doc']) ? (string) $_GET['doc'] : '';
$content = '';
$err = '';
if ($doc !== '') {
    $path = __DIR__ . '/docs/' . $doc; // 【漏洞点】doc 未限制在 docs 目录内；使用 ../ 可穿越到上级目录，读取服务器上其它可读文件（如 /flag）
    if (is_readable($path)) {
        $content = file_get_contents($path);
    } else {
        $err = '无法读取该路径。';
    }
}
?>
<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>案例：路径拼接读文件</title>
    <link rel="stylesheet" href="/assets/common.css">
</head>
<body>
<div class="wrap">
    <p><a href="/index.php">← 返回首页</a></p>
    <h1>文档查看（路径拼接）</h1>
    <p class="muted">看底部<strong>完整源码</strong>。当前目录是 <code>…/docs/</code>，要数清楚需要几个 <code>..</code> 才能指到系统里的敏感文件（本靶场 FLAG 在 <code>/flag</code>）。</p>
    <ul>
        <li><a href="?doc=readme.txt">readme.txt</a></li>
    </ul>
    <?php if ($err !== ''): ?>
        <p class="warn"><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>
    <?php if ($content !== ''): ?>
        <pre><?php echo htmlspecialchars($content, ENT_QUOTES, 'UTF-8'); ?></pre>
    <?php endif; ?>
    <div class="card">
        <h2>提示</h2>
        <p class="muted">从 <code>docs/</code> 出发，数清楚 <code>..</code> 层级后再拼目标文件路径。</p>
        <h2>可用 PoC 小卡片</h2>
        <pre>?doc=../../../../flag</pre>
    </div>

    <?php
    require_once __DIR__ . '/inc_show_source.php';
    audit_lab_show_page_source(__FILE__);
    ?>
</div>
</body>
</html>
