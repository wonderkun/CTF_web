#coding:utf-8
import requests
import re

def RAND_RANGE(__n, __min, __max,__tmax):
    return __min+(__max-__min+1.0)*(__n/(__tmax+1.0))

def shuffle(dstr,relist):
    
    strlen = len(dstr)
    dstr = bytearray(dstr)
    if strlen<=1:
        return
    n_left = strlen
    i=0
    while n_left:
        n_left -=1
        
        rnd_idx=relist[33+i]  #第34个值开始作为预测值
        i+=1
        rnd_idx=int(RAND_RANGE(rnd_idx,0,n_left,2147483647))                 
        if (rnd_idx!=n_left):
            temp = dstr[n_left]
            dstr[n_left] = dstr[rnd_idx]
            dstr[rnd_idx]=temp
    return dstr

header = {
    "Connection":"Keep-Alive"
}
def guess():
    s=requests.session()
    target='http://localhost:8888/index.php'
    relist= []
    for i in range(32):#0-31
        res = s.get(target,headers=header)
        relist.append(int(res.text))
    # 预测rand值
    for i in range(32,120): #
        relist.append((relist[i-3]+relist[i-31]) % 2147483647)
    print "预测的第33个",relist[32]
    res = s.get(target)
    print "第33个rand值:",res.text
    # 这里的第33个值用于测试的,从第34个值，开始做为str_shuffle的内容
    base='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
    xstr = shuffle(base,relist)
    print xstr
    res =s.post(target+'?guess='+xstr[0:32])
    print res.text
guess()