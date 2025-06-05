# TyJson
这是TTDF框架RESTAPI的独立版插件，用于输出站点 `Json` 数据

## 功能特性

 - 支持文章列表
 - 支持页面列表
 - 支持获取内容
 - 支持字段查询
 - 支持高级字段查询
 - 支持获取设置项

## 安装方法

下载插件上传至`plugins`目录下重命名为`TyJson`

启用插件访问`/ty-json/`

### 使用教程

全局参数

`page` 控制当前页码（默认值：`1`
`pageSize` 控制每页显示条数（默认值：`10`
`format` 控制返回格式，支持格式为`html`&`markdown`
`excerptLength` 控制文章摘要长度（默认值：`200`

| 调用  |                路由                | 其他参数&路由 |         描述         |
| :---: | :--------------------------------: | :-----------: | :------------------: |
|  Get  |             /ty-json/site                |     null      |     获取网站信息     |
|  Get  |             /ty-json/posts             |     null      |     获取文章列表     |
|  Get  |             /ty-json/pages             |     null      |     获取页面列表     |
|  Get  |        /ty-json/search/{string}        |    string     |     搜索文章列表     |
|  Get  |       /ty-json/category/{string}       |   mid, slug   |     获取分类列表     |
|  Get  |         /ty-json/tag/{string}          |   mid, slug   |     获取标签列表     |
|  Get  |       /ty-json/content/{string}        |   cid, slug   |     获取内容数据     |
|  Get  |          /ty-json/attachments          |     null      |     获取附件列表     |
|  Get  |           /ty-json/comments            |     post      |     获取评论列表     |
|  Get  |    /ty-json/comments/post/{string}     |    string     |   获取文章评论列表   |
|  Get  |     /ty-json/fields/post/{name}/{value}     |    string     |   获取字段文章列表   |
|  Get  | /ty-json/advancedFields/{name}/{value} |    string     | 获取高级字段查询列表 |
|  Get  |           /ty-json/options/            |     null      |    获取设置项列表    |
|  Get  |    /ty-json/options/{name}/{value}     |    string     |    获取设置项详情    |
|  Get  |         /ty-json/themeOptions          |     null      |  获取主题设置项列表  |
|  Get  |      /ty-json/themeOptions/{name}      |    string     |  获取主题设置项详情  |

### 字段查询
> TTDF内置了字段查询文章列表功能

#### 普通查询  

普通字段查询文章

```bash
GET /ty-json/fields/post/{name}/{value}
```

#### 高级查询

##### 复杂查询​​使用 JSON

```bash
GET /ty-json/advancedFields?conditions=[{"name":"color","value":"red"},{"name":"price","operator":">=","value":100}]
```

##### 模糊搜索​

```bash
GET /ty-json/advancedFields/title/%重要%?operator=LIKE
```

#### 查询运算符与值类型规范

##### 运算符对照表

| 运算符 | 名称     | 描述           | 使用示例            |
| ------ | -------- | -------------- | ------------------- |
| =      | 等于     | 完全匹配字段值 | `color=red`         |
| !=     | 不等于   | 排除指定值     | `color!=blue`       |
| >      | 大于     | 数值比较       | `price>100`         |
| <      | 小于     | 数值比较       | `price<200`         |
| >=     | 大于等于 | 数值比较       | `price>=100`        |
| <=     | 小于等于 | 数值比较       | `price<=200`        |
| LIKE   | 模糊匹配 | 支持通配符%    | `title LIKE %重要%` |

##### 值类型定义

| 类型  | 说明               | 典型应用场景      | 示例             |
| ----- | ------------------ | ----------------- | ---------------- |
| str   | 字符串（默认类型） | 文本字段精确匹配  | `category=tech`  |
| int   | 整型数字           | ID/数量等数值比较 | `views>1000`     |
| float | 浮点数字           | 价格等精确数值    | `price<=19.99`   |
| text  | 长文本             | 内容全文检索      | `content=查询词` |

#### 注意事项

 - 字段名和值区分大小写
 - 特殊字符需要进行 URL 编码
