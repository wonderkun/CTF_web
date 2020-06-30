### Hcorme 题解

题目代码非常的简单，有一个callback的接口，能够把请求参数输出，并且`Content-Type`是`text/html; charset=UTF-8`。

这个题目在chrome浏览器还支持xss-Auditor的时候(现在chrome浏览器已经不支持xss-Auditor了，所以难题就不存在了)，有两个问题需要解决：

1. 怎么bypass xss-Auditor 
2. 绕过CSP的限制 `add_header Content-Security-Policy "default-src 'self'; object-src 'none'; base-uri 'none';";`

当然，绕过 CSP 比较简单了，这就是个jsonp吗，自己引用自己就可以了 `?callback=<script/src=?callback=alert(1)></script>`，但是难点问题是怎么绕过 `xss-Auditor` 。

#### 绕过XSS-Auditor

想要绕过XSS-Auditor，就需要先了解一下它的工作原理： 

> XSS-Auditor是chrome浏览器为了防止反射性xss而设计的机制，主要工作原理就是字符串匹配，在语法解析阶段，Chrome 会逐一扫描文档中的标签，然后检查这些标签和属性，如果检查到危险的内容就会跟 URL 进行比较，如果 URL 中含有同样的危险数据，XSS Auditor 就会认为这是一个反射型 XSS，并加以拦截。

XSS-Auditor 有两种工作模式，`block`和`filter`, `block` 模式下拦截会丢给你一个异常页面，`filter` 模式则会将它觉得恶意的代码替换掉，本题目启用的是`block`模式。

既然是字符串匹配，那么绕过XSS-Auditor最简单的想法就是让 URL 中的内容和页面中出现的内容发生不一致，无法进行字符串匹配，但是页面中的内容依然可以被当做js执行。一种想法就是利用编码的问题，URL中采用一种编码，浏览器进行语法分析之前对服务器返回的内容进行了解码，变成了另外的内容之后才进入语法解析的流程，此时就不会发生匹配。

服务器返回头中的确定了`charset=UTF-8`，编码是页面编码`utf-8`，但是chrome浏览器会对服务器的返回体的编码进行探测，这个探测结果是可以覆盖掉`charset`的指定，方法就是利用 `Byte Order Mark`(BOM)字节。BOM字节是必须出现在返回体的最前面才是有效的，观察这个题目发现是可以控制返回体的前几个字节的，也就是说可以利用 BOM 字节来执行返回体的编码方式。

BOM 字节有如下四种，参考[https://simple.wikipedia.org/wiki/Byte_order_mark](https://simple.wikipedia.org/wiki/Byte_order_mark)。


| Bytes	| Encoding Form |
| ------ | --------------| 
| EF BB BF	| UTF-8 | 
| FE FF	| UTF-16, big-endian |
| FF FE	| UTF-16, little-endian | 
| 00 00 FE FF | UTF-32, big-endian |
| FF FE 00 00 |	UTF-32, little-endian | 


UTF-8肯定是不行的，因为它是兼容`ascii`编码的，然后经过测试发现 `UTF-16, big-endian` 和 `UTF-16, little-endian` 是可以进行xss的。 

```ipython

In [54]: print("%FF%FE" + quote(("<script/src=?callback=alert(1)></script>").encode('utf-16le')))
%FF%FE%3C%00s%00c%00r%00i%00p%00t%00/%00s%00r%00c%00%3D%00%3F%00c%00a%00l%00l%00b%00a%00c%00k%00%3D%00a%00l%00e%00r%00t%00%28%001%00%29%00%3E%00%3C%00/%00s%00c%00r%00i%00p%00t%00%3E%00

In [55]: print("%FE%FF" + quote(("<script/src=?callback=alert(1)></script>").encode('utf-16be')))
%FE%FF%00%3C%00s%00c%00r%00i%00p%00t%00/%00s%00r%00c%00%3D%00%3F%00c%00a%00l%00l%00b%00a%00c%00k%00%3D%00a%00l%00e%00r%00t%00%28%001%00%29%00%3E%00%3C%00/%00s%00c%00r%00i%00p%00t%00%3E

```
