<?php
	header("Content-Type:text/html;charset=utf-8");
	error_reporting(E_ERROR);
	require_once('../include/conf.php');
	require_once('../include/fiter.php');
	if($_SESSION['flag'] !== 1){
		header("location:../index.php");exit;
	}
?>
<!DOCTYPE html>
<html lang="zh-CN">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>网络流量监控平台</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="//cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="//cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
	<style type="text/css">
		html,body {
			height: 100%;
		}
		.box {
			filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#6699FF', endColorstr='#6699FF'); /*  IE */
			background-image:linear-gradient(bottom, #6699FF 0%, #6699FF 100%);
			background-image:-o-linear-gradient(bottom, #6699FF 0%, #6699FF 100%);
			background-image:-moz-linear-gradient(bottom, #6699FF 0%, #6699FF 100%);
			background-image:-webkit-linear-gradient(bottom, #6699FF 0%, #6699FF 100%);
			background-image:-ms-linear-gradient(bottom, #6699FF 0%, #6699FF 100%);
			
			margin: 0 auto;
			position: relative;
			width: 100%;
				height: 100%;
			}
		.login-box {
			width: 100%;
			max-width:500px;
			height: 400px;
			position: absolute;
			top: 50%;

			margin-top: -200px;
			/*设置负值，为要定位子盒子的一半高度*/
			
		}
		@media screen and (min-width:500px){
			.login-box {
				left: 50%;
				/*设置负值，为要定位子盒子的一半宽度*/
				margin-left: -250px;
			}
		}	

		.form {
			width: 100%;
			max-width:500px;
			height: 275px;
			margin: 25px auto 0px auto;
			padding-top: 25px;
		}	
		.login-content {
			height: 300px;
			width: 100%;
			max-width:500px;
			background-color: rgba(255, 250, 2550, .6);
			float: left;
		}		
			
			
		.input-group {
			margin: 0px 0px 30px 0px !important;
		}
		.form-control,
		.input-group {
			height: 40px;
		}

		.form-group {
			margin-bottom: 0px !important;
		}
		.login-title {
			padding: 20px 10px;
			background-color: rgba(0, 0, 0, .6);
		}
		.login-title h1 {
			margin-top: 10px !important;
		}
		.login-title small {
			color: #fff;
		}

		.link p {
			line-height: 20px;
			margin-top: 30px;
		}
		.btn-sm {
			padding: 8px 24px !important;
			font-size: 16px !important;
		}
		</style>
  </head>

  <body>

    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">Home's</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
		  <ul class="nav navbar-nav">
			<li class="active"><a href="#">首页</a></li>
			<li class="dropdown">
			<a href="#" class="dropdown-toggle" data-toggle="dropdown">导航<span class="caret"></span></a>
			<ul class="dropdown-menu" role="menu">
			  <li class="dropdown-header">系统功能</li>
			  <li><a href="#">信息建立</a></li>
			  <li><a href="#">信息查询</a></li>
			  <li><a href="#">信息管理</a></li>
			</ul>
			</li>
			<li><a href="#">帮助</a></li>
			<li><a href="logout.php">注销登录</a></li>
		  </ul>
        </div><!--/.navbar-collapse -->
      </div>
    </nav>

	<div class="box">
		<div class="login-box">
			<div class="text-center">
				<h1><small>实时监控</small></h1>
			</div>
			<form action="index.php" method="post">
				<input type="text" id="ord" name="ord" class="form-control" placeholder="ls ...">
				<button type="submit" class="btn btn-sm btn-info">执行</button>
			</form>
			<div class="login-content">
				<p>
				<?php 
				header("Content-Type:text/html;charset=utf-8");
				$o = new fiter();
				$a = $o->ord_clean($_POST['ord']);
				if($a){
					if(exec($a))echo '命令执行成功！！';
					else echo "命令执行出错！！";
				}else echo "想干啥呢~_~!!"
				?>
				<p>
			</div>
		</div>
	</div>
    <script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
    <script src="//cdn.bootcss.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
  </body>
</html>
