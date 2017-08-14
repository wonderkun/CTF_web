<?php 
// code by SEC@USTC 

echo '<html><head><meta http-equiv="charset" content="gbk"></head><body>'; 

$URL = $_SERVER['REQUEST_URI'];
 
// echo 'URL: '.$URL.'<br/>'; 
$flag = "CTF{this_is_flag}"; 

$code = str_replace($flag, 'CTF{???}', file_get_contents('./web1.php')); 
$stop = 0; 

//这道题目本身也有教学的目的 
//第一，我们可以构造 /indirection/a/../ /indirection/./ 等等这一类的 
//所以，第一个要求就是不得出现 ./ 
if($flag && strpos($URL, './') !== FALSE){ 
    $flag = ""; 
    $stop = 1;        //Pass 
} 

//第二，我们可以构造 \ 来代替被过滤的 / 
//所以，第二个要求就是不得出现 ../ 
if($flag && strpos($URL, '\\') !== FALSE){ 
    $flag = ""; 
    $stop = 2;        //Pass 
} 

//第三，有的系统大小写通用，例如 indirectioN/ 
//你也可以用?和#等等的字符绕过，这需要统一解决 
//所以，第三个要求对可以用的字符做了限制，a-z / 和 . 
$matches = array(); 

preg_match('/^([a-z0-9\/.]+)$/', $URL, $matches);

// print_r($matches);

if($flag && empty($matches) || $matches[1] != $URL){ 
    $flag = ""; 
    $stop = 3;        //Pass 
} 

//第四，多个 / 也是可以的 
//所以，第四个要求是不得出现 // 
if($flag && strpos($URL, '//') !== FALSE){ 
    $flag = ""; 
    $stop = 4;        //Pass 
} 

//第五，显然加上index.php或者减去index.php都是可以的 
//所以我们下一个要求就是必须包含/index.php，并且以此结尾 
if($flag && substr($URL, -9) !== '/web1.php'){ 
                            
    $flag = ""; 
    $stop = 5;        //Not Pass 
} 

//第六，我们知道在index.php后面加.也是可以的 
//所以我们禁止p后面出现.这个符号 
if($flag && strpos($URL, 'p.') !== FALSE){ 
    $flag = ""; 
    $stop = 6;        //Not Pass 
} 

//第七，现在是最关键的时刻 
//你的$URL必须与/indirection/index.php有所不同 
if($flag && $URL == '/shiyanba/web1.php'){ 
    $flag = ""; 
    $stop = 7;        //Not Pass 
} 
if(!$stop) $stop = 8; 

echo 'Flag: '.$flag."</br>"; 
echo 'Stop: '.$stop;

echo '<hr />'; 
for($i = 1; $i < $stop; $i++) 
    $code = str_replace('//Pass '.$i, '//Pass', $code); 
for(; $i < 8; $i++) 
    $code = str_replace('//Pass '.$i, '//Not Pass', $code); 


echo highlight_string($code, TRUE); 

echo '</body></html>';