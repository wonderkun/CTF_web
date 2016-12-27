<?php
/**
 * Created by PhpStorm.
 * User: pfven
 * Date: 2016/7/20
 * Time: 21:35
 */
include 'header.php';
if(isset($_GET["image"])){
    $file = $_GET['image'];
    $file = preg_replace("/[^a-zA-Z0-9.]+/","", $file);
    $file = str_replace("config","_", $file);
    echo $file;
    
    $txt = base64_encode(file_get_contents($file));


    echo "<img src='data:image/png;base64,".$txt."'></img>";
}else {
     header("Location: index.php?image=heihei.jpg");

    exit();
}

include 'footer.php';
//***