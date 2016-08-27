<?php
 
defined("DIR_PERMITION") or die("Permision denied!");

$userid=check_login();
if(!$userid){
    
    echo "<script>alert('not login!');</script>";
    echo("<script>location.href='./index.php?action=front&mode=login'</script>");
    die();

}else{
    $id=$_GET['id'];
    $TOKEN=$_GET['TOKEN'];
    if($TOKEN!=$_SESSION['CSRF_TOKEN']){
        die("token error!");
    }
    
    $sql="delete from  note where id='$id' and  userid='$userid' and id!='1'";
    // echo $sql;

    $res=mysql_my_query($sql);
    // var_dump($res);

    $res = $conn->affected_rows;
    if($res){
           echo "<script>alert('delete success!!')</script>";

    }else{
        echo "<script>alert('delete error!!')</script>";

    }

    echo "<script> history.back(-1);</script>";
}





