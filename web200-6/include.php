<html>
Tips: the parameter is file! :) 
<!-- upload.php -->
</html>
<?php
    @$file = $_GET["file"];
    if(isset($file))
    {
        if (preg_match('/http|data|ftp|input|%00/i', $file) || strstr($file,"..") !== FALSE || strlen($file)>=70)
        {
            echo "<p> error! </p>";
        }
        else
        {
            include($file.'.php');
        }
    }
?>
