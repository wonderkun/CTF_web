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

<form action="index.php?file=upload" method="post" enctype="multipart/form-data">
    <input type="file" name ="file">
    <input type="submit" name="submit" value="upload" >
</form>

<?php

if (isset($_FILES['file'])) {
    
    $seed = rand(0,getrandmax());
    mt_srand($seed);
    if ($_FILES["file"]["error"] > 0) {
        echo "<div class=\"msg error\" id=\"message\">
		<i class=\"fa fa-exclamation-triangle\">uplpad file error!:".$_FILES["file"]["error"]."</i></div>";
		die();
    }
    $fileTypeCheck = ((($_FILES["file"]["type"] == "image/gif")
            || ($_FILES["file"]["type"] == "image/jpeg")
            || ($_FILES["file"]["type"] == "image/pjpeg")
            || ($_FILES["file"]["type"] == "image/png"))
        && ($_FILES["file"]["size"] < 204800));
    $reg='/^gif|jpg|jpeg|png$/';
    $fileExtensionCheck=!preg_match($reg,pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
    
    if($fileExtensionCheck){
        die("Only upload image file!");
    }
    if($fileTypeCheck){
        
        $fileOldName = addslashes(pathinfo($_FILES['file']['name'],PATHINFO_FILENAME));
        $fileNewName = './Up10aDs/' . random_str() .'.'.pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION);
        $userid = $_SESSION['userid'];
        $sql= "insert into `download` (`uid`,`image_name`,`location`) values ($userid,'$fileOldName','$fileNewName')";
        $res = $conn ->query($sql);
        if($res&&move_uploaded_file($_FILES['file']['tmp_name'], $fileNewName)){
         echo "<script>alert('file upload success!');window.location.href='index.php?file=home'</script>";

        }else{
             echo "<script>alert('file upload error')</script>";
        }

    }else{

        echo "<script>alert('file  type error');</script>";
    }

}

?>
