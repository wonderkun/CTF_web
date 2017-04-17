<?php 
   defined("DIR_PERMITION") or die("Access denied!");
?> 

<link rel="stylesheet" href="./css/form.css" style="css" />
<div id="login">  
    <h1>Login</h1>
    <!-- index.php?file=register.php -->  
    <form method="post" action="index.php?file=login">  
        <input type="text" required="required" placeholder="用户名" name="username"></input>  
        <input type="password" required="required" placeholder="密码" name="password"></input>  
        <button class="but" type="submit">登录</button>

    </form>  
</div>

<?php 
$username = isset($_POST['username'])?$_POST['username']:die();
$password = isset($_POST['password'])?md5($_POST['password']):die();

$sql = "select * from user where username='$username' and password='$password'";

$res = $conn ->query($sql);
if($res->num_rows>0){
    
    $user = $res->fetch_assoc();
    $_SESSION['username'] = $user['username'];
    $_SESSION['userid'] = $user['id'];
    header('Location: index.php?file=home');
}else{

    echo '<script>alert("username or password error!);</script>';

}


?>