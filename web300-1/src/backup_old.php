<?php
require_once('encrypt.php');
file_put_contents('./backup.txt', token_encrypt(file_get_contents('./flag.txt')));

?>