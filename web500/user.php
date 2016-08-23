<?php
    include_once("common.php");
    $user = getUser();
    if($user == -1){
        header("Location: login.php");
        exit();
    }
?>
<html>
<head>
<meta charset="utf-8">
<title>用户</title>
<link rel="stylesheet" href="css/bootstrap.min.css" />
<link rel="stylesheet" href="css/bootstrap-theme.min.css" />
<script src="js/jquery-2.2.0.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<style>
    .container{
        max-width: 500px;
        margin: 0 auto;
    }
    img{
        width: 64px;
        float: left;
        margin-left: 10px;
        margin-top: 20px;
    }
    #upload{
        display: block;
        clear: both;
    }
</style>
<script>
    $(function(){
        $("#upload").click(function(){
            $("#pic").click();
        })
        $("#pic").on("change", function(){
            $("#upload_form").submit();
        })
    })
</script>
</head>
<body>
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">Blog</a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">
                <li class="active"><a href="index.php">首页<span class="sr-only">(current)</span></a></li>
                <li><a href="#">WWW</a></li>
                <li><a href="#">TTT</a></li>
                <li><a href="#">FFF</a></li>
                <li><a href="logout.php">退出</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li>
                <a href="index.php?act=user">
                <?php
                    echo $user ? $user : "root";
                ?>
                </a></li>
            </ul>
            </div><!-- /.navbar-collapse -->
            
            
        </div><!-- /.container-fluid -->
    </nav>
    <div class="container">
        <h2><?php 
            echo $user ? $user : "root";
        ?> 的相册</h2>
        <img class="img img-circle img-responsive" src="upload/avatar.png" />
        <img class="img img-circle img-responsive" src="upload/github.png" />
        <?php
            foreach ($piclist as $img) {
                echo '<img class="img img-circle img-responsive" src="' . $img .'" />';
            }
        ?>
        <div class="clear: both"></div>
        <form class="form-groups" id="upload_form" method="post" action="upload.php" enctype="multipart/form-data">
        <input type="file" id="pic" name="pic" style="display: none; " />
        <?php
            if(!$user){
                echo '<img class="img img-rounded" id="upload" style="border: 2px dashed black; cursor: pointer" src="upload/add.png" id="upload" />';
            }else{
                echo '<div style="display: block; text-align: center; clear: both;"><h2>管理员才可以上传图片！<br /><h6 style="color: grey">就是不让你传，你咬我啊</h6></h2>';
            }
        ?>
    </form>
    </div>

</body>
</html>