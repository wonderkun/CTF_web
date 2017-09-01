<?php
header('X-XSS-Protection:0');
header('Content-Type:text/html;charset=utf-8');
?>
<head>
<meta http-equiv="x-ua-compatible" content="IE=10">
</head>
<body>
<form action=''>
<input type='hidden' name='token' value='<?php
  echo htmlspecialchars($_GET['token']); ?>'>
<input type='submit'>
</body>
