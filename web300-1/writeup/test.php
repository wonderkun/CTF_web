<?php 

// $str = urldecode("wonderkun%80%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%98%00%00%00%00%00%00%00tadmin|1|fe46d7d8924ec23d69f8d01432de41aa");
// echo $str;
// echo "\n";
// echo strlen($str);

// echo "\n";
// echo md5("adminadminadmin".$str);


// 74657374
// 6b6c54356a45796d44795a6e7674746b

// 0da2efe4

function getRandChar($length){
	$str = null;
	$strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
	for($i=0;$i<$length;$i++){
		$n = rand(0, strlen($strPol) - 1);
		$str.=$strPol[$n];
	}
	return $str;
}
srand( 1492139553/ 300);
$iv = getRandChar(16);
echo bin2hex($iv);
echo "\n";

// $ php test.php  
// 516664694c6936513870656f55373270
// wonderkun@wonderkun-pc:~/github/CTF_web/web300-1/writeup$ cat content/backup.txt  
// 34018770e87f5195923a434ce1a8bb9defe76053fff2ea04af6adb70e3f7d3792f22889951bec6dddf32cfaa7a33d4a3wonderkun@wonderkun-pc:~/github/CTF_web/web300-1/writeup$ 