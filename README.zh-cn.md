# thrift-laravel

在Laravel中使用Thrift

# 如何使用

## 服务端

1. `composer require angejia/thrift-laravel`
2. 在配置`app.providers`中添加一行:
    ````
    \Angejia\Thrift\ThriftServiceProvider::class
    ````
3. 在配置文件`config/thrift.php`中,设置`thrift.providers`:
    ````
    // 第一个是 service name, 在thrift中定义
    // 第二个是实现该service的类全名,实现了thrift生成的接口
    // class ImageServcie implement \Angejia\ImageServiceIf
    ['Angejia.ImageService', \Angejia\ImageService::class],
    ````
4. 在`app\Http\Kernel`中添加 Middleware `\Angejia\Thrift\Middleware\ThriftServerMiddleware::class`

    默认会处理`/rpc`上的请求,如果需要更改此规则,请继承此类并覆盖`process`方法

## 客户端

1. `composer require angejia/thrift-laravel`
2. 在配置`app.providers`中添加一行:
    ````
    \Angejia\Thrift\ThriftServiceProvider::class
    ````
3. 在配置文件`config/thrift.php`中,设置`thrift.depends`:
    ````
    // key 为url
    // value 为 service name 的数组
    "http://localhost/rpc" => [
        'Angejia.ImageService',
    ]
    ````
4. 使用:
    ````
    /** @var \Angejia\Thrift\Contracts\ThriftClient $thriftClient */
    $thriftClient = app(\Angejia\Thrift\Contracts\ThriftClient::class);
    /** @var \Angejia\ImageServiceIf $imageService */
    $imageService = $thriftClient->with('Angejia.ImageService');
    
    $result = $imageService->foo();
    ````
