<?php
if(empty($_SESSION['name'])){
    session_start();
    #echo 'hello ' + $_SESSION['name'];
}else{
    die('you must login with admin');
}
?>