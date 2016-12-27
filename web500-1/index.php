<?php
include_once("common.php");
if(isset($_GET["act"]) && preg_match('/^[a-z0-9_]+$/is', $_GET["act"])) {
    include_once __DIR__ . "/" . $_GET["act"] . ".php";
    exit();
}
?>
<html>
    <head>
    <meta charset="utf-8">
    <title>首页</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/bootstrap-theme.min.css" />
    <script src="js/jquery-2.2.0.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
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
        <?php
            $user = getUser();
            if($user === -1){
                echo "<a href=\"login.php\">请登录";
            }else{
                echo "<a href=\"index.php?act=user\">";
                if($user === 0){
                    echo "root";
                }else{
                    echo $user;
                }
            }
        ?>
        </a></li>
      </ul>
    </div><!-- /.navbar-collapse -->
    
    
  </div><!-- /.container-fluid -->
</nav>
    <div class="container">
        <h1 style="text-align: center; margin-top: 25%;">你以为首页会有东西ᶘ ᵒᴥᵒᶅ<br />然而并没有(⊙ω⊙)<br />哈哈哈哈哈哈哈哈哈哈<br />!!!!!!</h1>
    </div>
    </body>
</html>
