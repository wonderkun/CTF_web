<?php

date_default_timezone_set('PRC');

$config = array(
	'rewrite' => array(
		'admin/index.html' => 'admin/main/index',
		'admin/<c>_<a>.html'    => 'admin/<c>/<a>', 
        	'<m>/<c>/<a>'          => '<m>/<c>/<a>',
		'<c>/<a>'          => '<c>/<a>',
		'/'                => 'main/index',
	),
);

$domain = array(
	"hardphp" => array( // 调试配置
		'debug' => 0,
		'mysql' => array(

				'MYSQL_HOST' => 'mysql',
				'MYSQL_PORT' => '3306',
				'MYSQL_USER' => 'root',
				'MYSQL_DB'   => 'hardphp',
				'MYSQL_PASS' => 'quqKzef8xN2Ey91X',
				'MYSQL_CHARSET' => 'utf8',

		),
	),
	"speedphp.com" => array( //线上配置
		'debug' => 0,
		'mysql' => array(),
	),
);
// 为了避免开始使用时会不正确配置域名导致程序错误，加入判断
if(empty($domain["hardphp"])) die("配置域名不正确，请确认hardphp的配置是否存在！");
return $domain["hardphp"] + $config;
