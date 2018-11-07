<?php
function filters($data){
    foreach($data as $key=>$value){
        if(preg_match('/eval|assert|exec|passthru|glob|system|popen/i',$value)){
            die('Do not hack me!');
        }
    }
}
?>