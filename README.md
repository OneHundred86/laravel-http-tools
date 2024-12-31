### 用于laravel框架的http开发的工具



#### 1.错误码配置
```php
// 编辑config/errorcode.php
return [
    'messages' => [
        0 => 'ok',
        1 => 'error',
        // ...
    ],
];
```

#### 2.json响应
```php
use Illuminate\Http\Request;
use Oh86\Http\Response\ErrorResponse;
use Oh86\Http\Response\OkResponse;

class XXXCtroller
{
    public function ok(Request $request)
    {
        return new OkResponse();
    }

    public function error(Request $request)
    {
        return new ErrorResponse(1, null, [
            'foo' => 'bar',
        ]);
    }
}
```

#### 3.异常
##### 3.1 错误码异常
```php
use Oh86\Http\Exceptions\ErrorCodeException;

throw new throw new ErrorCodeException(401, null, null, 401); // 响应错误码为401的json响应。
```

##### 3.2 http请求错误异常
```php
use Illuminate\Support\Facades\Http;
use Oh86\Http\Exceptions\HttpRequestException;

$url = 'https://api.test/test';
$datas = ['foo' => 'bar'];
$headers = ['X-Test' => 'test'];
$r = Http::withHeaders($headers)->get($url, $datas);

if ($response->json('code') !== 0) {
    // 将响应json响应给客户端
    throw new HttpRequestException(
        $r->status(),
        $r->body(),
        $url,
        $datas,
        $headers,
    );
}

```

#### 4.中间件
#### 4.1 互斥锁中间件
```php
use Illuminate\Support\Facades\Route;
use Oh86\Http\Middleware\MutexRequestByArg;

Route::get('sms/code', [SmsController::class, 'sendSmsCode'])
    ->middleware(MutexRequestByArg::class.':phone');
```

#### 5.基于token的会话状态
```php
use Oh86\Http\TokenSessions\AbstractTokenSession;

class TestToken extends AbstractTokenSession
{
    protected $storeKeyPrefix = 'TestToken';
}


// 首次生成token
$t = new TestToken(300);
$t->put('foo', 'bar');
// $t->save(); // auto save in destructor
$token = $t->getToken();


// 其他地方使用token
$t = TestToken::load($token);
$val = $t->get('foo');
$t->destroy();
```
