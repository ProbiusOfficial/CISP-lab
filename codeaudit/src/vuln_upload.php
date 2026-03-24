<?php
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['f'])) {
    $name = $_FILES['f']['name'] ?? '';
    $tmp = $_FILES['f']['tmp_name'] ?? '';
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'png', 'gif', 'php'], true)) { // 【漏洞点】后缀校验过弱，且把 php 也放行
        $target = __DIR__ . '/uploads/' . $name; // 【漏洞点】使用原始文件名，可能目录穿越/覆盖
        if (@move_uploaded_file($tmp, $target)) {
            $msg = '上传成功：' . $name;
        } else {
            $msg = '上传失败';
        }
    } else {
        $msg = '只允许 jpg/png/gif/php';
    }
}
?>
<!doctype html>
<html lang="zh-CN"><head><meta charset="UTF-8"><title>案例：文件上传</title><link rel="stylesheet" href="/assets/common.css"></head>
<body><div class="wrap">
<p><a href="/index.php">← 返回首页</a></p>
<h1>文件上传（校验薄弱）</h1>
<form method="post" enctype="multipart/form-data">
<input type="file" name="f"><button type="submit">上传</button>
</form>
<?php if ($msg !== ''): ?><p class="warn"><?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></p><?php endif; ?>
<div class="card">
<h2>提示</h2>
<p class="muted">关注后端允许的后缀和目标保存路径，思考双扩展与原始文件名风险。</p>
<h2>可用 PoC 小卡片</h2>
<pre>文件名示例：shell.php</pre>
</div>
<?php require_once __DIR__ . '/inc_show_source.php'; audit_lab_show_page_source(__FILE__); ?>
</div></body></html>
