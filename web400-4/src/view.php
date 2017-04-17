<?php 

defined("DIR_PERMITION") or die("Access denied!");
if(!isset($_SESSION['username'])||!isset($_SESSION['userid'])){
   header("Location: index.php?file=login"); 
   die();
   
}
?>

<link rel="stylesheet" href="./css/main.css" style="css" />
<div id="left"><div class="main"><table align=center  cellspacing="0" cellpadding="0" style="border-collapse: collapse;border:0px;">
<tr>
<form method=get action="index.php">
<td align=right style="padding:0px; border:0px; margin:0px;">
        <input type=submit name=file value="home" class="side-pan">
</td>
<td  align=right style="padding:0px; border:0px; margin:0px;" >
        <input type=submit name=file value="download" class="side-pan">
</td>
<td  align=right style="padding:0px; border:0px; margin:0px;" >
        <input type=submit name=file value="upload" class="side-pan">
</td>
</form></tr></table></div></div>
<div id="right"></div><div align=center>

<?php

$id =!empty($_GET['id'])?filter($_GET['id']):'1';

$sql = "select location from download where id='$id'"; 

$res = $conn->query($sql);
if($res->num_rows>0){
   $filename = $res->fetch_assoc();
   if(file_exists($filename['location'])){
    //    echo file_get_contents($filename['location']);

    echo "<img src=\"data:image/".pathinfo($filename['location'], PATHINFO_EXTENSION).";base64,".base64_encode(file_get_contents($filename['location']))."\">";

   }else{

       echo "<script>alert('file may be deleted!');window.location.href='index.php'</script>";
   }

}else{
    echo "<script>alert(\"sorry,can't find this picture!\");window.location.href='index.php'</script>";
}

