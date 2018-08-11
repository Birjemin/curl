# 关于
PHP-Curl是一个轻量级的网络操作类，实现GET、POST、PUT、DELETE常用操作，支持方法链写法。参考`https://github.com/wenpeng/curl`，取消了file文件的上传和下载，增加了put,delete方法，传输协议取消`form-data`。

# 需求
有时候仅仅是使用http请求get,post,put请求而已，使用`guzzlephp` composer包太重，所以参考了这个包做了一下简化。

# 依赖
PHP 5.6+

# 示例
```php
$curl = new Curl;
```
或者
```php
$curl = Curl::init();
```

#### GET:
```php
$curl->url(目标网址);
```

#### POST:
```php
$curl->post(变量名, 变量值)->post(多维数组)->url(目标网址);
```

#### PUT:
```php
$curl->put(变量名, 变量值)->put(多维数组)->url(目标网址);
```

#### DELETE:
```php
$curl->delete(变量名, 变量值)->delete(多维数组)->url(目标网址);
```

#### 配置
参考:http://php.net/manual/en/function.curl-setopt.php

```php
$curl->set('CURLOPT_选项', 值)->post(多维数组)->url(目标网址);
```

#### 自动重试
```php
// 出错自动重试N次(默认0)
$curl->retry(3)->post(多维数组)->url(目标网址);
```
#### content-type协议
默认协议`Content-Type:application/x-www-form-urlencoded`, 可使用`Content-Type:application/json`

#### 结果
```php
// 任务结果状态
if ($curl->error()) {
    echo $curl->message();
} else {
    // 任务进程信息
    $info = $curl->info();
    
    // 任务结果内容
    $content = $curl->data();
}

```

#### 参考
[https://github.com/wenpeng/curl](https://github.com/wenpeng/curl)

[https://laravel-china.org/articles/7956/a-small-pit-in-developing-api-with-laravel](https://laravel-china.org/articles/7956/a-small-pit-in-developing-api-with-laravel)
