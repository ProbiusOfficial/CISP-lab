<?php
/**
 * 仅允许本机访问的「内网敏感页」—— 外网用户直连通常看不到，但可通过 SSRF 由服务端代为请求。
 */
$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
$allow = ($ip === '127.0.0.1' || $ip === '::1');

if (!$allow) {
    header('HTTP/1.1 403 Forbidden');
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Forbidden: internal only (REMOTE_ADDR=' . htmlspecialchars($ip, ENT_QUOTES, 'UTF-8') . ')';
    exit;
}

header('Content-Type: text/plain; charset=UTF-8');
echo "INTERNAL_SECRET=This_page_is_only_visible_from_localhost\n";
echo "hint: combine with SSRF fetch.php\n";
