<?php
	$config['hostname'] = 'localhost';
	$config['username'] = 'root';
	$config['password'] = '123456';
	$config['database'] = 'hctf2017';

	$db = new mysqli($config['hostname'],$config['username'],$config['password'],$config['database']);
?>
