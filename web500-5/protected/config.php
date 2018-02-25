<?php

date_default_timezone_set('PRC');


$config = array(
	'rewrite' => array(
        '<m>/<c>/<a>'          => '<m>/<c>/<a>',
		'<c>/<a>'          => '<c>/<a>',
		'/'                => 'main/index',
	),
    'debug' => 1,
    'mysql' => array(

        'MYSQL_HOST' => 'localhost',
        'MYSQL_PORT' => '3306',
        'MYSQL_USER' => 'root',
        'MYSQL_DB'   => 'pwnhub_6670',
        'MYSQL_PASS' => '123456',
        'MYSQL_CHARSET' => 'utf8mb4',

    ),
);
return $config;
