<?php
$page = isset($_GET['page']) ? (string) $_GET['page'] : 'welcome';
$file = __DIR__ . '/pages/' . $page . '.php'; // 【漏洞点】page 未做白名单；可用 ../ 改变路径，从而包含 Web 根目录下其它 .php（如 secret_for_lab.php）
?>
<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>案例：动态包含</title>
    <link rel="stylesheet" href="/assets/common.css">
</head>
<body>
<div class="wrap">
    <p><a href="/index.php">← 返回首页</a></p>
    <h1>帮助子页（动态 include）</h1>
    <p class="muted">底部有<strong>完整源码</strong>。观察 <code>$file</code> 如何由 <code>page</code> 拼出来；再想想：若站点根目录还有别的 <code>.php</code>，能否被包含进来？</p>
    <ul>
        <li><a href="?page=welcome">welcome</a></li>
        <li><a href="?page=about">about</a></li>
    </ul>
    <div class="card">
        <?php
        if (is_file($file)) {
            include $file; // 【漏洞点】include 的路径受用户输入间接控制 → LFI / 可能 RFI（若配置允许远程）
        } else {
            echo '<p>页面不存在。</p>';
        }
        ?>
    </div>
    <div class="card">
        <h2>提示</h2>
        <p class="muted">注意后端会自动拼接 <code>.php</code>，尝试用目录穿越控制最终包含路径。</p>
        <h2>可用 PoC 小卡片</h2>
        <pre>?page=../secret_for_lab</pre>
    </div>

    <?php
    require_once __DIR__ . '/inc_show_source.php';
    audit_lab_show_page_source(__FILE__);
    ?>
</div>
</body>
</html>
