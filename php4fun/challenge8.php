<?php 
#GOAL: file_get_content('sbztz.php')    : )
   
    class just4fun {
        public $filename;
   
        function __toString() {
            return @file_get_contents($this->filename);
        }
    }
   
    $data = stripslashes($_GET['data']);
    if (!$data) {
        die("hello from y");
    }
   
    $token = $data[0];
    $pass = true; 
   
    switch ( $token ) {
        case 'a' :
        case 'O' :
        case 'b' :
        case 'i' :
        case 'd' :
            $pass = ! (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
            break;
   
        default:
            $pass = false;
   
    }
   
    if (!$pass) {
      die("TKS L.N.");
    }
   
    echo unserialize($data);
    