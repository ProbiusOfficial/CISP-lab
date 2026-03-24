<?php
/**
 * 漏洞：用户可控 URL 直接用于服务端请求（未校验协议与目标）。
 * 使用 cURL 以支持 http(s)、file、dict、gopher 等（libcurl 能力为准）。
 */
header('Content-Type: text/plain; charset=UTF-8');

$url = isset($_GET['url']) ? trim($_GET['url']) : '';

if ($url === '') {
    echo "缺少参数 url。\n";
    echo "示例：?url=http://127.0.0.1/secret.php\n";
    echo "      ?url=file:///flag\n";
    echo "      ?url=dict://127.0.0.1:6379/show\n";
    exit;
}

/**
 * @return string 响应正文或错误说明
 */
function ssrf_fetch($url)
{
    if (!function_exists('curl_init')) {
        return @file_get_contents($url) ?: "file_get_contents 失败\n";
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

    // 允许常见 SSRF 相关协议（PHP 7.3+ 常量）
    if (defined('CURLPROTO_ALL')) {
        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_ALL);
        curl_setopt($ch, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_ALL);
    } else {
        $p = CURLPROTO_HTTP | CURLPROTO_HTTPS | CURLPROTO_FILE;
        if (defined('CURLPROTO_DICT')) {
            $p |= CURLPROTO_DICT;
        }
        if (defined('CURLPROTO_GOPHER')) {
            $p |= CURLPROTO_GOPHER;
        }
        curl_setopt($ch, CURLOPT_PROTOCOLS, $p);
        curl_setopt($ch, CURLOPT_REDIR_PROTOCOLS, $p);
    }

    $body = curl_exec($ch);
    $errno = curl_errno($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($errno !== 0) {
        return "curl errno={$errno}: {$err}\n";
    }

    // dict 等非 HTTP 协议时 HTTP 码可能为 0，仍返回 body
    if ($body === false || $body === '') {
        return "(空响应) HTTP_CODE={$code}\n";
    }

    return $body;
}

echo ssrf_fetch($url);
