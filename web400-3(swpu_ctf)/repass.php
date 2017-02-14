<?php

require_once("common.php");
session_start();

if (@$_SESSION['login'] === 1)
{
    header('Location:/web/riji.php');
	exit();
}

if(@$check)
{
	@mysql_conn();
	$sql = "select * from user where name='$username'";
	$result = @mysql_fetch_array(mysql_query($sql));
	mysql_close();
	if (!empty($result))
	{
		if(base64_decode($check) == md5($result['salt']))
		{
			if($mibao == $result['check'])
			{
				$sql1 = "UPDATE user SET passwd=md5('$pass') where name='$username'";
				mysql_conn();
				if(mysql_query($sql1)){
					echo("<script>alert('Reset success!!')</script>");
					header('Location:/web/index.php');
				}
				else{
					echo("<script>alert('Reset wrong!!')</script>");
				}
				mysql_close();
			}
			else
			{
				echo("<script>alert('mibao wrong!!')</script>");
			}
		}
		else
		{
			echo("<script>alert('Check wrong!!')</script>");
		}
	}
	else
	{
		echo("<script>alert('Username wrong!!')</script>");
	}
}
else
{
	echo("<script>alert('Check wrong!!')</script>");
}
?>