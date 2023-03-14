symfony cache for webman
##  安装
```php
composer require yzh52521/symfony-cache
```

##  配置文件

config/cache.php

##  支持驱动
- apc
- array
- file 本地缓存
- redis 缓存
- memcached 缓存
- database 数据库缓存


## 示例

```php
namespace app\controller;

use support\Request;
use yzh52521\SymfonyCache\Cache;

class UserController
{
    public function db(Request $request)
    {
        $key = 'test_key';
        Cache::set($key, rand());
        return response(Cache::get($key));
    }
   
}
```
## 访问多个缓存存储

```php
$value = Cache::store('file')->get('foo');

Cache::store('redis')->set('bar', 'baz', 600); // 10 Minutes
```
## 从缓存中检索
```php
$value = Cache::get('key');

$value = Cache::get('key', 'default');

```
## 检查是否存在
```php
if (Cache::has('key')) {
    //
}
```

## 删除缓冲
```php
Cache::delete('key');
```








