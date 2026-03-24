# SSRF（服务端请求伪造）演示靶场

基于 **Nginx + PHP 7.3**，目录 `ssrf-lab/`。漏洞接口 **`fetch.php?url=`** 将用户输入交给 **libcurl** 执行，未校验协议与目标，用于演示：

- **`file://` 读文件**
- **`dict://` 协议探测内网端口**
- **`127.0.0.1` 的多种写法绕过**（黑名单场景）
- **`gopher://` 向内网服务发送原始 TCP 载荷**（本环境以 **Redis RESP** 为例）

容器内已启动 **Redis（127.0.0.1:6379）**，便于对比「开放端口」与「关闭端口」的返回差异。

---

## 环境说明

- 映射端口：**8086 → 80**（`docker-compose`）
- FLAG：写入 **`/flag`**（环境变量 `DASFLAG` / `FLAG` / `GZCTF_FLAG`，否则默认演示字符串）
- **Redis**：入口脚本启动 `redis-server --bind 127.0.0.1 --port 6379`

---

## 快速启动

```bash
cd ssrf-lab
docker build -t ssrf-lab .
docker run -d --name ssrf-lab -p 8086:80 ssrf-lab
```

访问：`http://127.0.0.1:8086/`

---

## 1、file:// 读文件

- `?url=file:///flag`：读动态 FLAG 文件  
- `?url=file:///etc/passwd`：读系统文件（视权限）

依赖 PHP/cURL 对 `file://` 的支持（本镜像默认可用）。

---

## 2、dict:// 扫内网端口

`dict` 会连接指定 `host:port` 并发送 DICT 协议数据；目标若不是 DICT 服务，往往返回错误信息或连接失败，可**对比**判断端口是否开放：

- **开放且为 Redis**：`dict://127.0.0.1:6379/show` 等常有可读错误/回显  
- **开放且为 HTTP**：`dict://127.0.0.1:80/show` 与纯关闭端口表现不同  
- **关闭**：常见 `curl errno` 为连接被拒绝、超时等（见 `fetch.php` 纯文本输出）

---

## 3、127.0.0.1 绕过

当业务对 `127.0.0.1` 做黑名单时，可尝试（**是否生效取决于 URL 解析组件**，需实测）：

| 示例 | 说明 |
|------|------|
| `http://127.1/secret.php` | 环回地址省略写法 |
| `http://2130706433/secret.php` | 十进制整型 IP（127.0.0.1） |
| `http://0177.0.0.1/secret.php` | 八进制第一段 |
| `http://0x7f.0x00.0x00.0x01/secret.php` | 十六进制分段 |

本靶场 `secret.php` 仅允许本机访问；上述 URL 若被服务端成功请求，可看到与直连浏览器（403）不同的结果。

---

## 4、gopher:// 与 Redis

Gopher URL 中，**`_` 之后**为发往 TCP 的原始载荷（需 URL 编码）。示例为 RESP 的 **PING**：

```text
gopher://127.0.0.1:6379/_*1%0d%0a%244%0d%0aPING%0d%0a
```

成功时 Redis 常返回 `+PONG`（具体以 `fetch.php` 回显为准）。

---

## 漏洞代码

`src/fetch.php`：对用户传入 URL 使用 `curl_exec()`，并设置 `CURLOPT_PROTOCOLS` / `CURLOPT_REDIR_PROTOCOLS` 为 **`CURLPROTO_ALL`**（教学用，等价于未限制协议）。

---

## 修复建议

- 白名单：仅允许业务需要的 **主机 + 协议 + 路径**  
- 禁止或剥离 `file://`、`dict://`、`gopher://` 等非必要协议  
- 出站防火墙、禁止访问内网段与云元数据地址（如 `169.254.169.254`，视环境）  
- 独立出站代理与审计  

---

## 与 CSRF 的区别

- **CSRF**：借用户浏览器与 Cookie，伪造**用户对业务站**的请求。  
- **SSRF**：借**服务器**发起请求，攻击内网与本地。

---

## 声明

仅用于**合法授权**的安全教学与实验，请勿用于未授权系统。
