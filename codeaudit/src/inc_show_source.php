<?php
declare(strict_types=1);

/**
 * 在页面底部输出当前脚本的语法高亮源码（供对照漏洞点注释阅读）。
 */
function audit_lab_show_page_source(string $scriptPath): void
{
    if (!is_readable($scriptPath)) {
        return;
    }
    $code = file_get_contents($scriptPath);
    if ($code === false) {
        return;
    }
    $highlighted = highlight_string($code, true);
    echo '<section class="source-section">';
    echo '<h2>本页源码（对照下方注释中的「漏洞点」）</h2>';
    echo '<p class="muted">注释格式：<code>// 【漏洞点】…</code> 标在问题代码旁，便于你从输入到危险函数跟一条数据流。</p>';
    echo '<div class="source-php">' . $highlighted . '</div>';
    echo '</section>';
}
