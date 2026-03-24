<?php
/**
 * 本页没有接真实数据库：用「拼出来的 SQL 字符串」+ 简单逻辑模拟查询结果，
 * 方便你练习：从 $_GET['q'] 一直跟到拼接处，想想真实场景里这条字符串会交给谁执行。
 */
$q = isset($_GET['q']) ? (string) $_GET['q'] : '';
$allRows = [
    ['id' => 1, 'username' => 'admin', 'note' => '内部备注：重置码 8821'],
    ['id' => 2, 'username' => 'guest', 'note' => '无'],
];
$sql = '';
$rows = [];
if ($q !== '') {
    $sql = "SELECT id, username, note FROM users WHERE username = '" . $q . "'"; // 【漏洞点】将用户输入直接拼进 SQL，真实环境中应由数据库执行 → 易产生注入；应改为预处理语句 / 参数绑定
    // 下面用正则粗略模拟「恒真条件」导致返回多行，对应典型 payload 思路（如 ' OR '1'='1）
    if (preg_match("/'\s*OR\s*'1'\s*=\s*'1/i", $q) || preg_match('/\bOR\s+1\s*=\s*1\b/i', $q)) {
        $rows = $allRows;
    } else {
        foreach ($allRows as $r) {
            if ($r['username'] === $q) {
                $rows[] = $r;
            }
        }
    }
}
?>
<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>案例：SQL 拼接</title>
    <link rel="stylesheet" href="/assets/common.css">
</head>
<body>
<div class="wrap">
    <p><a href="/index.php">← 返回首页</a></p>
    <h1>用户查询（SQL 拼接）</h1>
    <p class="muted">先拉到页面最下方看<strong>完整源码</strong>。试着改参数 <code>q</code>：怎样让「拼接后的 SQL」出现永真条件？修复思路：永远不要让用户输入以字符串拼接方式进入 SQL。</p>
    <form method="get">
        <label>用户名 <input type="text" name="q" value="<?php echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>"></label>
        <button type="submit">查询</button>
    </form>
    <?php if ($sql !== ''): ?>
        <h2>当前拼接出的 SQL（便于你对照输入）</h2>
        <pre><?php echo htmlspecialchars($sql, ENT_QUOTES, 'UTF-8'); ?></pre>
    <?php endif; ?>
    <?php if ($q !== ''): ?>
        <h2>返回的数据（模拟）</h2>
        <?php if ($rows): ?>
            <table>
                <tr><th>id</th><th>username</th><th>note</th></tr>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars((string) $row['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string) $row['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string) $row['note'], ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p class="muted">没有匹配行（正常查询只按用户名精确相等筛选）。</p>
        <?php endif; ?>
    <?php endif; ?>
    <div class="card">
        <h2>提示</h2>
        <p class="muted">先看「拼接后的 SQL」再改参数，观察引号闭合后条件如何变化。</p>
        <h2>可用 PoC 小卡片</h2>
        <pre>?q=admin' OR '1'='1</pre>
    </div>

    <?php
    require_once __DIR__ . '/inc_show_source.php';
    audit_lab_show_page_source(__FILE__);
    ?>
</div>
</body>
</html>
