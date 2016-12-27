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
    
    function addflash($content){   
        $replace=""; 
        $reg=array('/\|/','/or/i','/sleep/i','/benchmark/i');
        while (TRUE){
        $flag=false;
        foreach ($reg as $key => $value) {
            # code... 
            if (preg_match($value,$content)){
                $flag=true;
                $content=preg_replace($value,$replace,$content);
            }
        }         
        if(!$flag){
            break;
            }  
        }
        return $content;
    }
    
    mysql_connect(DB_HOST, DB_USER, DB_PASS);
    mysql_select_db(DB_NAME);
    mysql_query("set names utf8");
    
    $id = mysql_real_escape_string(htmlspecialchars($_GET['id']));
    $id=addflash($id);
    $sql = "update " . TB_PREFIX . "note set isdeleted=1 where user='{$_SESSION['user']}' and id={$id}";
    mysql_query($sql);
    // echo mysql_error();
    
    echo '删除成功，查看<a href="trash.php">垃圾桶</a>';
    
?>