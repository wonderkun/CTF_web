<?php
	header("Content-Type:text/html;charset=utf-8");
	error_reporting(E_ERROR);
	define ('PATH_WEB', dirname(__FILE__).'/');
	require_once(dirname(__FILE__).'/include/conf.php');
	require_once(dirname(__FILE__).'/include/fiter.php');
	#var_dump($_SESSION);
	if($_SESSION['flag'] === 1){
		header("location:./admin/");exit;
	}
	#echo $_POST['uname'].'````'.$_POST['passwd'];
	
	if($_POST['uname'] && $_POST['passwd']){
		$obj = new fiter();
		$uname = $obj->sql_clean($_POST['uname']);
		$passwd = md5($_POST['passwd']);
		$query="SELECT * FROM admin WHERE uname='".$uname."'";
		$result=mysql_query($query);
		#var_dump($result);
		if ($row = mysql_fetch_array($result)){
			#print_r($row);echo "\n\r<br/>";
            if ($row['passwd']===$passwd){
				$_SESSION['flag'] = 1;
				#echo $_SESSION['flag'];
				header("location:./admin/");exit();
			}
            else{
				echo "<script> alert('password error!!@_@');parent.location.href='index.php'; </script>"; exit();
            }
        }
		else{
			echo "<script> alert('username error!!@_@');parent.location.href='index.php'; </script>"; exit();
		}
		
	}
	else {
		echo "<script> alert('username and password must have a value!!@_@');parent.location.href='index.php'; </script>"; exit();
	}
?>
