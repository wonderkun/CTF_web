### 考察python urllib 头注入  

**要求python版本,我本地用的是python2.7.5,可以复现漏洞**

### 题目来源
[http://blog.csdn.net/niexinming/article/details/53024755](http://blog.csdn.net/niexinming/article/details/53024755)  

### writeup 
关于http头注入,请参考[http://www.tuicool.com/articles/2iIj2eR](http://www.tuicool.com/articles/2iIj2eR )
+ changepasswd.py 是后台修改redis密码的脚本
+ poc.py 是利用脚本 