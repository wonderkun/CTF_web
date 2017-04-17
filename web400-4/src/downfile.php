<?php 

include("config.php");

$_POST = d_addslashes($_POST);
$_GET = d_addslashes($_GET);

if(!isset($_SESSION['username'])||!isset($_SESSION['userid'])){
	  header("Location: index.php?file=login");
	  die();
}

function file_download($download)
{
	if(file_exists($download))
				{
					header("Content-Description: File Transfer"); 

					header('Content-Transfer-Encoding: binary');
					header('Expires: 0');
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header('Pragma: public');
					header('Accept-Ranges: bytes');
					header('Content-Disposition: attachment; filename="image.'.pathinfo($download,PATHINFO_EXTENSION).'"'); 
					header('Content-Length: ' . filesize($download));
					header('Content-Type: application/octet-stream'); 
					ob_clean();
					flush();
					readfile ($download);
				}
				else
				{
				 echo "<script>alert('file may be deleted');window.location.href='index.php?file=download'</script>";	
				}
	
}

$imageid = isset($_POST['image'])?filter($_POST['image']):die();

$sql = "select location from download where (uid=0 and id='$imageid') or uid=".$_SESSION['userid']." and id=$imageid";

$res = $conn-> query($sql);
if($res->num_rows>0){
    $file = $res->fetch_assoc(); //找第一条记录 
    $filename = $file['location'];
    file_download($filename);
}else{
    echo "<script>alert('picture can't be find!);windows.location.href='index.php?file=download'</script>";
}

?>