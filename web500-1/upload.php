<?php
include_once("common.php");
$user = getUser();
if($user == -1){
    die("没登录吧骚年，我觉得你有必要提高一下自己的姿势水平");
}
if($user){
    die("说了不让传，你还传！");
}
if($_FILES["pic"]["error"] == 0) {
    if($_FILES["pic"]['size'] > 0 && $_FILES["pic"]['size'] < 102400) {
        $typeAccepted = ["image/jpeg", "image/gif", "image/png"];
        $blackext = ["php", "php5", "php3", "php4", "php7", "html", "swf", "htm"];
        $filearr = strtolower(pathinfo($_FILES["pic"]["name"]));
        if(!in_array($_FILES["pic"]['type'], $typeAccepted)) {
            exit("淘气！");
        }
        if(in_array($filearr["extension"], $blackext)) {
            exit("淘气！");
        }
        $filename = "upload/" . randmd5() . "." . $filearr["extension"];
        if(move_uploaded_file($_FILES["pic"]["tmp_name"], $filename)) {
            array_push($piclist, $filename);
            setcookie("piclist", serialize($piclist), time() + 60 * 60 * 24 * 30);
            header("Location: index.php?act=user");
            exit();
        } else {
            echo "upload error!";
        }
    }
} else {
    echo "没文件啊 naive！";
}

