<?php
    include_once("common.php");
    if(isset($_POST["title"]) && isset($_POST["content"])){
        mysql_connect(DB_HOST, DB_USER, DB_PASS);
        mysql_select_db(DB_NAME);
        mysql_query("set names utf8");
        
        $title = mysql_real_escape_string(htmlspecialchars($_POST["title"]));
        $content = mysql_real_escape_string(htmlspecialchars($_POST["content"]));
        
        $user = $_SESSION["user"];
        
        $sql = "insert into " . TB_PREFIX . "note (title, content, user) values('{$title}', '{$content}', '{$user}')";
        
        mysql_query($sql);
        var_dump(mysql_error());
        
        echo '新建笔记成功，查看<a href="index.php">我的笔记</a>';
        exit ;
    }
?>
<html>
    <head>
        <meta charset="utf-8" />
        <title>新建笔记-NS云笔记</title>
    </head>
    <body>
        <a href="index.php">我的笔记</a>&nbsp;&nbsp;&nbsp;
        <a href="new_note.php">新建笔记</a>&nbsp;&nbsp;&nbsp;
        <a href="trash.php">垃圾桶</a>
        <form method="POST" action="new_note.php">
            <p>标题</p>
            <input type="text" name="title" />
            <p>内容</p>
            <textarea name="content"></textarea><br /><br />
            <input type="submit" name="submit" value="submit" />
        </form>
    </bdoy>
</html>