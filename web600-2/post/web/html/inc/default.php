<?php 
include 'inc/post.php';
?>
<?php
    if (isset($_POST["title"])) {
        $attachments = array();
        if (isset($_FILES["attach"]) && is_array($_FILES["attach"])) {
            
            $folder = sha1(random_bytes(10));
            mkdir("../uploads/$folder");
            for ($i = 0; $i < count($_FILES["attach"]["tmp_name"]); $i++) {
                if ($_FILES["attach"]["error"][$i] !== 0) continue;
                $name = basename($_FILES["attach"]["name"][$i]);
                move_uploaded_file($_FILES["attach"]["tmp_name"][$i], "../uploads/$folder/$name");
                $attachments[] = new Attachment("/uploads/$folder/$name");
            }
        }
        $post = new Post($_POST["title"], $_POST["content"], $attachments);
        $post->save();
    }
    if (isset($_GET["action"])) {
        if ($_GET["action"] == "restart") {
            Post::truncate();
            header("Location: /");
            die;
        } else {
?>
<h2>Create new post</h2>
<form method="POST" enctype="multipart/form-data">
<table>
<tr>
<td>
<label for="title">Title</label>
</td> <td>
<input name="title">
</td>
</tr>
<tr>
<td>
<label for="content">Content</label>
</td> <td>
<input name="content">
</td>
</tr>
<tr>
<td>
<label for="attach">Attachments</label>
</td> <td>
<input name="attach[]" type="file">
</td>
</tr>
<tr>
<td>
</td> <td>
<input name="attach[]" type="file">
</td>
</tr>
<tr>
<td>
</td> <td>
<input name="attach[]" type="file">
</td>
</tr>
<tr><td></td><td>
<input type="submit">
</td></tr>
</table>
</form>
<?php 
            }
    }

    $posts = Post::loadall();
    if (empty($posts)) {
        echo "<b>You do not have any posts. Create <a href=\"/?action=create\">some</a>!</b>";
    } else {
        echo "<b>You have " . count($posts) ." posts. Create <a href=\"/?action=create\">some</a> more if you want! Or <a href=\"/?action=restart\">restart your blog</a>.</b>";
    }

    foreach($posts as $p) {
        echo $p;
        echo "<br><br>";
    }
    
?>

