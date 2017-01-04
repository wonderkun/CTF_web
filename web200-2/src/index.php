<?php
if(isset($_GET) && !empty($_GET)){
    $url = $_GET['file'];
    $path = "upload/".$_GET['path'];
    
}else{
    show_source(__FILE__);
    exit();
}

if(strpos($path,'..') > -1){
    die('This is a waf!');
}


if(strpos($url,'http://127.0.0.1/') === 0){
    file_put_contents($path, file_get_contents($url));
    echo "console.log($path update successed!)";
}else{
    echo "Hello.Geeker";
}

