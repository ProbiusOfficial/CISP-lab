<?php
$name = isset($_POST['name']) ? (string) $_POST['name'] : '';
$greeting = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $greeting = '你好，' . $name . '！'; // 【漏洞点】把原始昵称拼进将要输出到 HTML 的字符串，未做 HTML 转义
}
?>
<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>案例：XSS</title>
    <link rel="stylesheet" href="/assets/common.css">
</head>
<body>
<div class="wrap">
    <p><a href="/index.php">← 返回首页</a></p>
    <h1>欢迎语（输出上下文）</h1>
    <p class="muted">看底部<strong>完整源码</strong>。对比：输入框的 <code>value</code> 已转义，而欢迎语那一段没有 —— 若昵称里含 HTML/脚本标签会发生什么？修复：在 HTML 正文里输出用户数据时用 <code>htmlspecialchars(..., ENT_QUOTES, 'UTF-8')</code>，并配合 CSP 等纵深防御。</p>
    <form method="post">
        <label>昵称 <input type="text" name="name" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"></label>
        <button type="submit">提交</button>
    </form>
    <?php if ($greeting !== ''): ?>
        <div class="card"><?php
        // 【漏洞点】将用户相关字符串直接输出到 HTML，未转义 → 反射型 XSS
        echo $greeting;
        ?></div>
    <?php endif; ?>
    <div class="card">
        <h2>提示</h2>
        <p class="muted">对比输入框 <code>value</code>（已转义）与欢迎语输出（未转义）的差别。</p>
        <h2>可用 PoC 小卡片</h2>
        <pre>&lt;script&gt;alert(1)&lt;/script&gt;</pre>
    </div>

    <?php
    require_once __DIR__ . '/inc_show_source.php';
    audit_lab_show_page_source(__FILE__);
    ?>
</div>
</body>
</html>
