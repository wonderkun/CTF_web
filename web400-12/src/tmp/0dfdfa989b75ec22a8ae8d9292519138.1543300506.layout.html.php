<?php if(!class_exists("View", false)) exit("no direct access allowed");?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>HARDPHP</title>

<link href="/static/css/bootstrap.min.css" rel="stylesheet">
<link href="/static/css/style.css" rel="stylesheet">
<script src="/static/js/jquery.min.js"></script>
<script src="/static/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container">
<?php include $_view_obj->compile($__template_file); ?>
</div>
</body>
</html>