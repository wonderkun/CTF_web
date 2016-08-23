<html>
    <head>
        <meta charset="utf-8" />
        <title>
            删除笔记-NS云笔记
        </title>
    </head>
    <body>
<?php
    include_once("common.php");
    if(!isset($_GET["id"])){
        header("Location: index.php");
        exit;
    }
    mysql_connect(DB_HOST, DB_USER, DB_PASS);
    mysql_select_db(DB_NAME);
    mysql_query("set names utf8");
    
    $id = intval($_GET["id"]);
    
    $sql = "update " . TB_PREFIX . "note set isdeleted=0 where user='{$_SESSION['user']}' and id={$id}";
    mysql_query($sql);
    echo '恢复成功，去<a href="index.php">我的笔记查看</a>';
?>