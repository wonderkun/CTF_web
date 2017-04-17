<?php
define('DB_HOST', 'localhost');
define('DB_USER', trim(file_get_contents('/etc/db-user')));
define('DB_PASS',trim(file_get_contents('/etc/db-pass')));
define('DB_DATABASE', 'blog');
?>