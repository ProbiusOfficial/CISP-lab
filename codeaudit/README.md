# PHP 代码审计练习靶场

目录为 `代码审计/`。适合你练习：**读 PHP 源码**、**跟数据流**、**认识常见漏洞类型**（注入、包含、路径穿越、XSS、反序列化、上传、SSRF、CSRF、越权、逻辑漏洞），并对照每页底部的「完整源码」与行内 **`// 【漏洞点】`** 注释学习。

---

## 一、启动方式

### docker build / run

```bash
cd 代码审计
docker build -t code-audit-lab .
docker run -d --name code-audit-lab -p 8087:80 code-audit-lab
```

浏览器访问：`http://127.0.0.1:8087`

### docker-compose

```bash
cd 代码审计/docker
docker-compose up -d
```

---

## 二、页面一览

每个 `vuln_*.php` 页面**底部**会显示该文件**完整源码**（语法高亮），并与正文中的 **`// 【漏洞点】`** 注释对应。

| 路径 | 文件 | 你在练什么 |
|------|------|------------|
| `/` | `index.php` | 入口与练习说明 |
| `/vuln_sqli.php` | `vuln_sqli.php` | SQL 字符串拼接 |
| `/vuln_cmd.php` | `vuln_cmd.php` | `shell_exec` 与命令拼接 |
| `/vuln_include.php` | `vuln_include.php` | 动态 `include` |
| `/vuln_readfile.php` | `vuln_readfile.php` | 路径拼接读文件 |
| `/vuln_xss.php` | `vuln_xss.php` | 输出到 HTML 未转义 |
| `/vuln_unserialize.php` | `vuln_unserialize.php` | 不安全反序列化 |
| `/vuln_upload.php` | `vuln_upload.php` | 上传校验薄弱 |
| `/vuln_ssrf.php` | `vuln_ssrf.php` | 服务端请求伪造 |
| `/vuln_csrf.php` | `vuln_csrf.php` | 敏感操作无 CSRF Token |
| `/vuln_auth.php` | `vuln_auth.php` | 越权与鉴权绕过 |
| `/vuln_logic.php` | `vuln_logic.php` | 业务逻辑漏洞 |
| （辅助） | `secret_for_lab.php` | 可与包含漏洞配合阅读 |

---

## 三、目录结构

```
代码审计/
├── Dockerfile
├── docker/docker-compose.yaml
├── config/nginx.conf
├── service/docker-entrypoint.sh
├── README.md
└── src/
    ├── index.php
    ├── inc_show_source.php
    ├── assets/
    ├── pages/
    ├── uploads/
    ├── data/
    ├── docs/
    └── vuln_*.php ...
```

