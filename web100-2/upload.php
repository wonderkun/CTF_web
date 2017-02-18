<form action="" enctype="multipart/form-data" method="post" 
name="upload">file:<input type="file" name="file" /><br> 
<input type="submit" value="upload" /></form>

<?php
if(!empty($_FILES["file"]))
{
    echo $_FILES["file"];
    $allowedExts = array("gif", "jpeg", "jpg", "png");
    @$temp = explode(".", $_FILES["file"]["name"]);
    $extension = end($temp);
    if (((@$_FILES["file"]["type"] == "image/gif") || (@$_FILES["file"]["type"] == "image/jpeg")
    || (@$_FILES["file"]["type"] == "image/jpg") || (@$_FILES["file"]["type"] == "image/pjpeg")
    || (@$_FILES["file"]["type"] == "image/x-png") || (@$_FILES["file"]["type"] == "image/png"))
    && (@$_FILES["file"]["size"] < 102400) && in_array($extension, $allowedExts))
    {
        move_uploaded_file($_FILES["file"]["tmp_name"], "upload/" . $_FILES["file"]["name"]);
        echo "file upload successful!Save in:  " . "upload/" . $_FILES["file"]["name"];
    }
    else
    {
        echo "upload failed!";
    }
}
?>
