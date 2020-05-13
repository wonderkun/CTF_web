<?php
$obj = new \CppClass;
$ret2 = $obj->index();
$ret = 0;
if($ret2 === 0)
    $ret = $obj->login();
else
    $obj = $ret2;

if($ret!==0 && $ret!==2){
    $_COOKIE['S'] = $ret;

}
echo $obj->render();
echo "<!-- ./html.zip --!>";
?>