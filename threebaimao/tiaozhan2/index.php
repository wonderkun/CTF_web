<?php 


if(isset($_GET['source'])){ 
    highlight_file(__FILE__); 
    exit; 
} 

// include_once("flag.php");

 
/* 
    shougong check if the $number is a palindrome number(hui wen shu) 
*/ 
function is_palindrome_number($number) { 
    $number = strval($number); 
    $i = 0; 
    $j = strlen($number) - 1; 
    while($i < $j) { 
        if($number[$i] !== $number[$j]) { 
            return false; 
        } 
        $i++; 
        $j--; 
    } 
    return true; 
} 

// ini_set("display_error", false); 
// error_reporting(0); 


$info = ""; 
$req = [];
 
foreach([$_GET, $_POST] as $global_var) { 
    foreach($global_var as $key => $value) { 
        $value = trim($value); 
        is_string($value) && is_numeric($value) && $req[$key] = addslashes($value); 
    } 
} 


$n1 = intval($req["number"]);

$n2 = intval(strrev($req["number"])); 

echo $n1."</br>";
echo $n2;


if($n1 && $n2) { 


    if ($req["number"] != intval($req["number"])) { 
        
        $info = "number must be integer!";
         
    } elseif ($req["number"][0] == "+" || $req["number"][0] == "-") { 

        $info = "no symbol"; 
    } elseif ($n1 != $n2) { //first check 

        $info = "no, this is not a palindrome number!"; 
    } else { //second check   $n1==$n2  
        
        if(is_palindrome_number($req["number"])) { //不能是回文数字  
            
            $info = "nice! {$n1} is a palindrome number!"; 
        } else {

          if(strpos($req["number"], ".") === false && $n1 < 2147483646) { //不允许有 .  

              $info = "find another strange dongxi: " . FLAG2; 
          } else {
                
              $info = "find a strange dongxi: " . FLAG; 
              
          } 
        } 
    } 

} else { 
    $info = "no number input~"; 
} 

echo $info;

?>