<html>
    <head>
        <meta charset="utf-8" />
        <title>
            笔记内容-NS云笔记
        </title>
    </head>
    <body>
    <a href="index.php">我的笔记</a>&nbsp;&nbsp;&nbsp;
    <a href="new_note.php">新建笔记</a>&nbsp;&nbsp;&nbsp;
    <a href="trash.php">垃圾桶</a>
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
    if($id == 1){
        $sql = 'select title, content from ' . TB_PREFIX . 'note where id=1';
    }else{
        $sql = "select title, content from " . TB_PREFIX . "note where id={$id} and user='{$_SESSION['user']}'";
    }
    $result = mysql_query($sql);
    $row = mysql_fetch_array($result);
    if(!$row){
        die("你没有权限查看该笔记");
    }
    
    echo "<h3>{$row['title']}</h3>";
    echo "<p>{$row['content']}</p>";
?>
    </body>
</html>