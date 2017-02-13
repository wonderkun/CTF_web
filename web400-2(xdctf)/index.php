<?php
/**
 * Created by PhpStorm.
 * User: phithon
 * Date: 15/10/14
 * Time: 下午7:46
 */

?>

<!DOCTYPE html>
<html>
<head>
    <title>file manage</title>
    <base href="./">
    <meta charset="utf-8" />
</head>
<body>
    <h3>Control</h3>
    <ul style="list-style: none;">
        <li><a href="./delete.php">Delete file</a></li>
        <li><a href="./rename.php">Rename file</a></li>
    </ul>

    <h3>Content</h3>
    <form action="./upload.php" method="post" enctype="multipart/form-data">
        <input type="file" name="upfile">
        <input type="submit" value="upload file">
    </form>
</body>
</html>