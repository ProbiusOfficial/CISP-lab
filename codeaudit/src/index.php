<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>PHP 代码审计练习靶场</title>
    <link rel="stylesheet" href="/assets/common.css">
</head>
<body>
<div class="wrap">
    <h1>PHP 代码审计练习</h1>

    <h2>怎么练</h2>
    <p>打开下面任意一个链接，先<strong>滚到该页最下方</strong>阅读<strong>完整 PHP 源码</strong>；源码里用 <code>// 【漏洞点】</code> 标出了关键危险用法。再结合页面上方的表单或参数，自己走一遍：数据从哪儿进、最后进了哪个函数。</p>

    <h2>练习页面</h2>
    <ul class="audit">
        <li><a href="/vuln_sqli.php"><code>vuln_sqli.php</code></a> — 把输入拼进 SQL 字符串</li>
        <li><a href="/vuln_cmd.php"><code>vuln_cmd.php</code></a> — 把输入拼进系统命令</li>
        <li><a href="/vuln_include.php"><code>vuln_include.php</code></a> — 用户输入影响 <code>include</code> 的路径</li>
        <li><a href="/vuln_readfile.php"><code>vuln_readfile.php</code></a> — 路径拼接导致越权读文件</li>
        <li><a href="/vuln_xss.php"><code>vuln_xss.php</code></a> — 往 HTML 里输出时未转义</li>
        <li><a href="/vuln_unserialize.php"><code>vuln_unserialize.php</code></a> — 不安全反序列化</li>
        <li><a href="/vuln_upload.php"><code>vuln_upload.php</code></a> — 文件上传校验薄弱</li>
        <li><a href="/vuln_ssrf.php"><code>vuln_ssrf.php</code></a> — 服务端请求伪造（SSRF）</li>
        <li><a href="/vuln_csrf.php"><code>vuln_csrf.php</code></a> — 敏感操作缺少 CSRF Token</li>
        <li><a href="/vuln_auth.php"><code>vuln_auth.php</code></a> — 越权/鉴权绕过</li>
        <li><a href="/vuln_logic.php"><code>vuln_logic.php</code></a> — 业务逻辑漏洞</li>
    </ul>

    <h2>自查时可以问自己</h2>
    <ul class="audit">
        <li>外部输入来自哪里？（例如 <code>$_GET</code>、<code>$_POST</code>、<code>$_COOKIE</code>、部分 <code>$_SERVER</code> 键）</li>
        <li>这些值有没有进 SQL、命令行、文件路径、<code>include</code>，或未经转义就进 HTML？</li>
        <li>若你要修掉它，会采用白名单、参数绑定、路径规范化、输出编码中的哪一种？</li>
    </ul>
</div>
</body>
</html>
