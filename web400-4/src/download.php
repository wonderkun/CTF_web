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

echo '
   <table width="40%" cellspacing="0" cellpadding="0" class="tb1" style="opacity: 0.6;">
   <tr><td width="20%" align=center style="padding: 10px;" >ID</td><td width="30%" align=center style="padding: 10px;">景点</td><td width="30%" align=center style="padding: 10px;">浏览</td><td width="30%" align=center style="padding: 10px;">收藏</td></tr></table>
   <table width="40%" cellspacing="0" cellpadding="0" class="tb1" style="margin:10px 2px 10px;opacity: 0.6;" >
  ';

$userid = $_SESSION['userid'];
$run="select * from download where uid='$userid' or uid='0'";
$result = mysqli_query($conn, $run);
    if (mysqli_num_rows($result) > 0) 
    {		
        while($row = mysqli_fetch_assoc($result)) 
            {
                
                echo '<tr><td width="20%" align=center style="padding: 10px;" >'.$row['id'].'</td>
                            <td width="40%" align=center style="padding: 10px;">'.htmlspecialchars($row['image_name'],ENT_QUOTES).'</td>
                            <td width="40%" align=center style="padding: 10px;"><a href="index.php?file=view&id='.$row['id'].'" target="">查看</a></td>
                            <td width="30%" align=center style="padding: 10px;">
                            <form method=post action="downfile.php" STYLE="margin: 0px; padding: 0px;">
                            <input type=hidden name=image value="'.$row['id'].'">
                            <input  type=submit class=download name=image_download value="收藏">
                            </form>
                        </td>
                    </tr>';
            }
    }
    echo '</table>';