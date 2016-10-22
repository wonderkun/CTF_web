<?php 

#GOAL: get the secret;
   
class just4fun {
    var $enter;
    var $secret;
}
   
if (isset($_GET['pass'])) {
    $pass = $_GET['pass'];
   
    if(get_magic_quotes_gpc()){
        $pass=stripslashes($pass);
    }
   
    $o = unserialize($pass);
   
    if ($o) {
        $o->secret = "?????????????????????????????";
        if ($o->secret === $o->enter)
            echo "Congratulation! Here is my secret: ".$o->secret;
        else
            echo "Oh no... You can't fool me";
    }
    else echo "are you trolling?";
}
