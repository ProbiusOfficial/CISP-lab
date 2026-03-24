<?php
/**
 * XXE 演示：不安全解析 XML
 * PHP 7.2+ 默认 libxml_disable_entity_loader(true)，此处为复现 XXE 显式关闭。
 */
header('Content-Type: text/html; charset=UTF-8');

$hasExpect = extension_loaded('expect');

if (function_exists('libxml_disable_entity_loader')) {
    libxml_disable_entity_loader(false);
}

libxml_use_internal_errors(true);

$raw = file_get_contents('php://input');
$contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

if (stripos($contentType, 'application/xml') !== false || stripos($contentType, 'text/xml') !== false) {
    $xml = $raw;
} elseif (isset($_POST['xml'])) {
    $xml = $_POST['xml'];
} else {
    $xml = $raw;
}

$xml = is_string($xml) ? $xml : '';

if (trim($xml) === '') {
    echo '<p>未收到 XML 内容。</p><p><a href="/index.php">返回</a></p>';
    exit;
}

$flags = LIBXML_DTDLOAD | LIBXML_NOENT;

$dom = new DOMDocument();
$ok = @$dom->loadXML($xml, $flags);

if (!$ok) {
    $errs = libxml_get_errors();
    libxml_clear_errors();
    echo '<h3>XML 解析失败</h3><pre>';
    foreach ($errs as $e) {
        echo htmlspecialchars(trim($e->message)) . "\n";
    }
    echo '</pre><p><a href="/index.php">返回</a></p>';
    exit;
}

// 将展开实体后的文本内容回显（文件读取内容会出现在此处）
$text = '';
$root = $dom->documentElement;
if ($root) {
    $text = $root->textContent;
}

$expanded = $dom->saveXML();
?>
<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>解析结果</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f4f5f7; color: #1f2937; }
        .wrap { max-width: 900px; margin: 28px auto; padding: 0 16px; }
        .card { background: #fff; border-radius: 10px; padding: 18px; box-shadow: 0 4px 12px rgba(0,0,0,.08); margin-bottom: 14px; }
        pre { white-space: pre-wrap; word-break: break-all; background: #111827; color: #e5e7eb; padding: 12px; border-radius: 8px; }
        a { color: #2563eb; }
        .muted { color: #6b7280; font-size: 14px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <h2>服务端解析结果（XXE 回显）</h2>
        <p class="muted">PECL expect：<strong><?php echo $hasExpect ? '已加载' : '未加载'; ?></strong>（<code>expect://</code> 依赖该扩展）</p>
        <p class="muted">若使用 file:// / php://filter 读取敏感文件，内容可能出现在「根元素纯文本」或下方展开后的 XML 中。</p>
        <h3>根元素 textContent</h3>
        <pre><?php echo htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); ?></pre>
        <h3>展开后的 XML（saveXML）</h3>
        <pre><?php echo htmlspecialchars($expanded, ENT_QUOTES, 'UTF-8'); ?></pre>
        <p><a href="/index.php">返回首页</a></p>
    </div>
</div>
</body>
</html>
