<?php
header("Content-Type:text/html;charset=utf-8");
header("X-Content-Type-Options: nosniff");
header("X-FRAME-OPTIONS: DENY");
header("X-XSS-Protection: 0");
?>
<html>
<head>
<meta charset="utf-8">
<script src="https://code.jquery.com/jquery-3.2.1.js" integrity="sha256-DZAnKJ/6XZ9si04Hgrsxu/8s717jcIzLy3oi35EouyE=" crossorigin="anonymous"></script>
<script>
window.jQuery || document.write('<script src="http://xianzhi.aliyun.com/jquery.js"><\/script>');
</script>
</head>
<body>
<script>
    $(function(){
        try { $(location.hash) } catch(e) {}
    })

</script>
</body>
</html>