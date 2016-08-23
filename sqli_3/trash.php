<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>首页-NS云笔记</title>
    </head>
    <body>
    <a href="index.php">我的笔记</a>&nbsp;&nbsp;&nbsp;
    <a href="new_note.php">新建笔记</a>&nbsp;&nbsp;&nbsp;
    <a href="trash.php">垃圾桶</a>
    
    <p>已删除笔记： </p>
    <a href="note.php?id=1">测试笔记</a>&nbsp;&nbsp;<a href="#">恢复</a>(测试笔记不可恢复)
<?php
    include_once("common.php");
    mysql_connect(DB_HOST, DB_USER, DB_PASS);
    mysql_select_db(DB_NAME);
    mysql_query("set names utf8");
    
    $sql = "select id,title from " . TB_PREFIX . "note where user='{$_SESSION['user']}' and isdeleted=1";
    $result = mysql_query($sql);

    while($row = mysql_fetch_array($result)){
        $has_note = true;
        echo "<a href=\"note.php?id={$row['id']}\">{$row['title']}</a>&nbsp;&nbsp;<a href=\"recovery.php?id={$row['id']}\">恢复</a>";
    }
    
?>
    
    </body>
</html>