<?php

require_once("common.php");
session_start();

if (@$_SESSION['login'] === 1){
    header('Location:/web/riji.php');
	exit();
}
class admin {
	var $name;
	var $check;
	var $data;
	var $method;
	var $userid;
	var $msgid;

	function check(){
		$username = addslashes($this->name);//进入数据库的数据进行转义
		@mysql_conn();
		$sql = "select * from user where name='$username'";
		$result = @mysql_fetch_array(mysql_query($sql));
		mysql_close();
		if(!empty($result)){
			//利用 salt 验证是否为该用户
			if($this->check === md5($result['salt'] . $this->data . $username)){
				echo '(=-=)!!';
				if($result['role'] == 1){//检查是否为admin用户
					return 1;
				}
				else{
					return 0;
				}
			}
			else{
				return 0;
			}
		}
		else{
			return 0;
		}
	}

	function do_method(){
		if($this->check() === 1){
			if($this->method === 'del_msg'){
				$this->del_msg();
			}
			elseif($this->method === 'del_user'){
				$this->del_user();
			}
			else{
				exit();
			}
		}
	}

	function del_msg(){
		if($this->msgid)
		{
			$msg_id = intval($this->msgid);//防注入
			@mysql_conn();
			$sql1 = "DELETE FROM msg where id='$msg_id'";
			if(mysql_query($sql1)){
				echo('<script>alert("Delete message success!!")</script>');
				exit();
			}
			else{
				echo('<script>alert("Delete message wrong!!")</script>');
				exit();
			}
			mysql_close();
		}
		else{
			echo('<script>alert("Check Your msg_id!!")</script>');
			exit();
		}
	}

	function del_user(){
		if($this->userid){
			$user_id = intval($this->userid);//防注入
			if($user_id == 1){
				echo('<script>alert("Admin can\'t delete!!")</script>');
				exit();
			}
			@mysql_conn();
			$sql2 = "DELETE FROM user where userid='$user_id'";
			if(mysql_query($sql2)){
				echo('<script>alert("Delete user success!!")</script>');
				exit();
			}
			else{
				echo('<script>alert("Delete user wrong!!")</script>');
				exit();
			}
			
			mysql_close();
		}
		else{
			echo('<script>alert("Check Your user_id!!")</script>');
			exit();
		}
	}
}

$a = unserialize(base64_decode($api));
$a->do_method();
?>