<?php

error_reporting(0);

foreach(Array("_POST","_GET","_COOKIE") as $key){
	foreach($$key as $k => $v){
		if(is_array($v)){
		die("hello,hacker!");
		}else{
			$k[0] !='_'?$$k = addslashes($v):$$k = "";
		}
	}
}
function mysql_conn()
{
	$conn=mysql_connect('localhost','root','123456') or die('could not connect'.mysql_error());
	mysql_query('use ctf');
	mysql_query("SET character_set_connection=utf8, character_set_results=utf8,character_set_client=utf8", $conn);
	return $conn;
}
function get_salt( $length = 16 ) { 
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'; 
	$salt =''; 
	for ( $i = 0; $i < $length; $i++ ) 
	{  
		$salt .= $chars[ mt_rand(0, strlen($chars) - 1) ]; 
	} 
	return $salt;
} 
function display($arr)
{
	$a = '
			<!--时锟斤拷-->
              <div class="shiguang animated bounceIn">
                <div class="left sg_ico">
                <img src="images/my_1.jpg" width="120" height="120" alt=""/>
                </div>
                <div class="right sg_text">
                <img src="images/left.png" width="13" height="16" alt="锟斤拷图锟斤拷"/>'
				.
                        $arr
				.
                '</div>
                <div class="clear"></div>
              </div>
              <!--时锟斤拷 end-->';
	return $a;
}
?>