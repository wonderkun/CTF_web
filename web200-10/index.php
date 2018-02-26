<?php 
if(!$_GET['site']){ 
	echo " 
<html> 
<body> 
look source code: 
<form action='' method='GET'> 
<input type='submit' name='submit' /> 
<input type='text' name='site' style=\"width:1000px\" value=\"https://www.baidu.com\"/> 
</form>
</body>
</html> ";
	die(); 
}

$url = $_GET['site']; 
$url_schema = parse_url($url); 
$host = $url_schema['host']; 
$request_url = $url."/"; 

if ($host !== 'www.baidu.com'){ 
	die("wrong site"); 
}

$ci = curl_init();
curl_setopt($ci, CURLOPT_URL, $request_url);
curl_setopt($ci, CURLOPT_RETURNTRANSFER, 1);
$res = curl_exec($ci);
curl_close($ci);

if($res){ 
	echo "<h1>Source Code:</h1>"; 
	echo $request_url; 
	echo "<hr />"; 
	echo htmlentities($res); 
}else{ 
	echo "get source failed"; 
} 

?>