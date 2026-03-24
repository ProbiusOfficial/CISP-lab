<?php
$host = isset($_GET['host']) ? (string) $_GET['host'] : '';
$output = '';
if ($host !== '') {
    $cmd = 'ping -c 1 ' . $host . ' 2>&1'; // 【漏洞点】用户输入拼进整条 shell 命令 → 可用分号、管道等注入另一条命令
    $output = shell_exec($cmd); // 【漏洞点】执行上述字符串；能不用 shell 就不用，若必须则严格白名单并避免拼接
}
?>
<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>案例：命令注入</title>
    <link rel="stylesheet" href="/assets/common.css">
</head>
<body>
<div class="wrap">
    <p><a href="/index.php">← 返回首页</a></p>
    <h1>主机探测（命令行拼接）</h1>
    <p class="muted">看页面底部<strong>完整源码</strong>。思考：<code>host</code> 里除了 IP/域名还能塞什么符号？本环境为 Alpine，可用 <code>ping</code>。对照 <code>shell_exec</code> 与字符串拼接。</p>
    <form method="get">
        <label>主机名或 IP <input type="text" name="host" value="<?php echo htmlspecialchars($host, ENT_QUOTES, 'UTF-8'); ?>" placeholder="127.0.0.1"></label>
        <button type="submit">Ping</button>
    </form>
    <?php if ($output !== ''): ?>
        <h2>命令输出</h2>
        <pre><?php echo htmlspecialchars($output, ENT_QUOTES, 'UTF-8'); ?></pre>
    <?php endif; ?>
    <div class="card">
        <h2>提示</h2>
        <p class="muted">先输入正常主机名，再尝试分隔符拼接第二条命令，对比输出差异。</p>
        <h2>可用 PoC 小卡片</h2>
        <pre>?host=127.0.0.1;id</pre>
    </div>

    <?php
    require_once __DIR__ . '/inc_show_source.php';
    audit_lab_show_page_source(__FILE__);
    ?>
</div>
</body>
</html>
