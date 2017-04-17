<?php 
   defined("DIR_PERMITION") or die("Access denied!");
?>
     <link rel="stylesheet" href="./css/form.css" style="css" />
    <div id="login">  
        <h1>Register</h1>  
        <form method="post" action="index.php?file=register">  
            <input type="text" required="required" placeholder="用户名" name="username"></input>  
            <input type="password" required="required" placeholder="密码" name="password"></input>  
            <button class="but" type="submit">注册</button>  
        </form>  
    </div>  
    
<?php
$username = isset($_POST['username'])?$_POST['username']:die();
$password = isset($_POST['password'])?md5($_POST['password']):die();

$sql = "select * from user where username='$username'";
$res = $conn->query($sql);

if($res->num_rows>0){

    die("<script>alert('username is already exist!')</script>");
}

$sql = "insert into `user` (`username`,`password`) values ('$username','$password')";

$res=$conn ->query($sql);

if($res){
    echo '<script>alert("register success!");window.location.href="index.php?file=login"</script>';
}else{
    die('<script>alert("register failed!")</script>');
}
?>