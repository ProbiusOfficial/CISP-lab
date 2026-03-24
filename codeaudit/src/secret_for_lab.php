<?php
/**
 * 模拟「不应通过 Web 直接访问的配置片段」。
 * 与 vuln_include.php 的目录穿越配合做课堂演示。
 */
// 仅定义变量，无输出（被 include 时可能暴露变量名，属另一审计点）
$LAB_INTERNAL_API_KEY = 'sk-audit-demo-do-not-use';
