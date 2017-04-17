<?php 
define("DIR_PERMITION",time());
include("config.php");
$_POST = d_addslashes($_POST);
$_GET = d_addslashes($_GET);

?>

<html>
<head>
<title>大美西安</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

</head>
<body>

<?php


$file  = isset($_GET['file'])?$_GET['file']:"home";
// echo $file;

if(preg_match('/\.\.|^[\s]*\/|^[\s]*php:|filter/i',$file)){
	   echo "<div class=\"msg error\" id=\"message\">
		<i class=\"fa fa-exclamation-triangle\"></i>Attack Detected!</div>";
		die();
}

$filename = $file.".php";

if(!include($filename)){
    
	if(!isset($_SESSION['username'])||!isset($_SESSION['userid'])){
	  header("Location: index.php?file=login");
	  die();
    }

    echo '<link rel="stylesheet" href="./css/main.css" style="css" />';
	
	echo '<div id="left"><div class="main"><table align=center  cellspacing="0" cellpadding="0" style="border-collapse: collapse;border:0px;">
	<tr>
	<form method=get action="index.php">
	<td align=right style="padding:0px; border:0px; margin:0px;">
			<input type=submit name=file value="home" class="side-pan">
	</td>
	<td  align=right style="padding:0px; border:0px; margin:0px;" >
			<input type=submit name=file value="download" class="side-pan">
	</td>
	<td  align=right style="padding:0px; border:0px; margin:0px;" >
			<input type=submit name=file value="upload" class="side-pan">
	</td>
	</form></tr></table></div></div>
	<div id="right"></div><div align=center>';
	
	echo '<br><br><font size=5>西安 --- 神奇的旅游胜地，十三朝古都。
	一块古老的土地，历史老人曾镌刻了无数的辉煌； 一座年轻的城市，时代之神正编织着美丽的梦想。
	西安，古称长安，是当年意大利探险家马可·波罗笔下《马可·波罗游记》中著名的古丝绸之路的起点。罗马哲人奥古斯都说过“一座城市的历史就是一个民族的历史”。
	西安，这座永恒的城市，就像一部活的史书，一幕幕，一页页记录着中华民族的沧桑巨变。';
	
}
    
?>

</body>
</html>


