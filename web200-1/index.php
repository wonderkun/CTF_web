<?php 

if(!isset($_GET['guess'])){
    echo rand();
}else{
    $randStr = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'),0,32);
    if($_GET['guess']===$randStr){
        echo "Success!";
    }
}