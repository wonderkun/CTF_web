<?php
$miwen="a1zLbgQsCESEIqRLwuQAyMwLyq2L5VwBxqGA3RQAyumZ0tmMvSGM2ZwB4tws";

function encode($str){
    $_o=strrev($str);
    // echo $_o;
        
    for($_0=0;$_0<strlen($_o);$_0++){
       
        $_c=substr($_o,$_0,1);
        $__=ord($_c)+1;
        $_c=chr($__);
        $_=$_.$_c;
        
        
    }
    
    return str_rot13(strrev(base64_encode($_)));
    
}

function   decode($miwen){
    $de_rot13=str_rot13($miwen);
    $de_strev=strrev($de_rot13);
    $de_base64=base64_decode($de_strev);
    $de_base64=strrev($de_base64);
    
    $mingwen="";
    for($i=0;$i<strlen($de_base64);$i++){
        $tmp=substr($de_base64,$i,1);
        $tmp=ord($tmp)-1;
        $tmp=chr($tmp);
        $mingwen.=$tmp;
        
        
    }
    
    return $mingwen;
    
    
    
}

echo decode($miwen);


?>