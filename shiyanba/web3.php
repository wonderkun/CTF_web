<?php
$username  = "this_is_secret"; 
$password  = "this_is_not_known_to_you"; 
$flag = "{this_is_flag}"; 

$info = isset($_GET['info'])? $_GET['info']: "" ;
$data_unserialize = unserialize($info);
if ($data_unserialize['username']==$username&&$data_unserialize['password']==$password){
    echo $flag;
}else{
    echo "username or password error!";

}

?>