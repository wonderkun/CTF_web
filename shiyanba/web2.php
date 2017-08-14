<?php  



$filename=__FILE__; 
$flag = "{this_is_flag}";
extract($_GET); 
if(isset($shiyan)){ 
    $content=trim(file_get_contents($filename)); 
    if($shiyan==$content) { echo $flag; } 
    else{ echo'Oh.no';} }

?>