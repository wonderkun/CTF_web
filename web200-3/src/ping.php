<!DOCTYPE html>
<html>
<head lang="en">
  <meta charset="UTF-8">
  <title>Login Page | NPUSEC</title>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="format-detection" content="telephone=no">
  <meta name="renderer" content="webkit">
  <meta http-equiv="Cache-Control" content="no-siteapp" />
  <link rel="alternate icon" type="image/png" href="assets/i/favicon.png">
  <link rel="stylesheet" href="assets/css/amazeui.min.css"/>
  <style>
    .header {
      text-align: center;
    }
    .header h1 {
      font-size: 200%;
      color: #333;
      margin-top: 30px;
    }
    .header p {
      font-size: 14px;
    }
  </style>
</head>
<body>
<div class="header">
  <div class="am-g">
    <h1>NPUSEC 网络管理系统</h1>
    <p>为写出好漏洞而生</p>
  </div>
  <hr/>
</div>
<div class="am-g">
  <div class="am-u-lg-6 am-u-md-8 am-u-sm-centered">
    <h3>管理</h3>
    <hr>

    <form method="post"  action ="#" class="am-form">
      <label for="ip">想探测的ip:</label>
      <input type="text" name="ip" id="ip" value="">
      <br>
      <br />
      <div class="am-cf">
        <input type="submit" name="" value=" ping " class="am-btn am-btn-primary am-btn-sm am-fl">
      </div>
    </form>
    <hr>

	<?php
    include('config.php');
    session_start();
    error_reporting(0);
    if(!$_SESSION['username']||!$_SESSION['status']){

        die("<script>alert('请登陆!!');window.location.href='./';</script>") ;
    }
    echo "flag{7246d06e237829198edbda64eb4770a1}"; //部署时重新设定

    $ip=isset($_POST['ip'])?$_POST['ip']:die();
    // $ip =isset($_GET['ip'])?$_GET['ip']:die();
    if(!preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/i",$ip)){
          die("<pre>ip 格式错误!!</pre>");
    }
    $substitutions = array(
		'&'  => '',
		';' => '',
		'$'  => '',
		'('  => '',
		')'  => '',
		'`'  => '',
		'|' => '',
 	);
   $ip = str_replace( array_keys( $substitutions ), $substitutions, $ip );
  //  echo strlen($ip)."</br>";
  //  echo $ip;
   if(strlen($ip)<7||strlen($ip)>15){
       die("<pre>ip 长度错误!</pre>");
   }
    $dir = 'sandBox/'.$_SERVER['REMOTE_ADDR'];
    if(!file_exists($dir)) mkdir($dir);
    chdir($dir);
    
    $comments = <<<INFO
   <!--
      \$dir = 'sandBox/'.\$_SERVER['REMOTE_ADDR'];
    if(!file_exists(\$dir)) mkdir(\$dir);
    chdir(\$dir);  
   -->
INFO;
    echo $comments;

    if( stristr( php_uname( 's' ), 'Windows NT' ) ) {
    		// Windows
    		$cmd = shell_exec( 'ping  ' . $ip );
    }else{
    		// *nix
            $cmd = shell_exec( 'ping  -c 1 ' . $ip );
    }
    	// Feedback for the end user
    	echo  "<pre>$cmd</pre>";
    ?>
    <p>© 2017 NPUSEC.</p>
  </div>
</div>
</body>
</html>
