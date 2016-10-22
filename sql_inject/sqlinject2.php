<?php
/**
 * Created by PhpStorm.
 * User: pfven
 * Date: 2016/7/20
 * Time: 11:09
 */
include 'header.php';?>
<form class="form-signin" action="" method="POST">
    <h2 class="form-signin-heading">Please sign in</h2>
    <label for="username" class="sr-only">Username</label>
    <input type="text" id="username" name="username" class="form-control" placeholder="Username" required autofocus>
    <label for="password" class="sr-only">Password</label>
    <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
    <?php
    session_start();
    require('db.php');
    $flag="xxxxxxxxxxxxxxxxxxxxxxxxxxxxx";
    if ((isset($_POST['username'])&&isset($_POST['password'])))
    {
        try
        {
            $user=trim(str_replace(" ","",$_POST['username']));
            $user=str_replace("*","",$user);
            $user=str_replace(";","",$user);
            $user=str_replace("#","",$user);
            $user=str_replace("\r","",$user);
            $user=str_replace("\n","",$user);
            $user=str_replace(urldecode("%09"),"",$user);
            $user=str_replace(urldecode("%0b"),"",$user);
            $user=str_replace(urldecode("%0c"),"",$user);
            $user=str_replace(urldecode("%0d"),"",$user);


            $pwd=md5($_POST['password']);
            $query="SELECT password FROM admin WHERE username='".$user."'";

            $result=$pdo->query($query);
            if ($result!=null&&$result->rowCount()!==0)
            {
                while($row = $result->fetch())
                {
                    if ($row['password']===$pwd)
                        echo $flag;
                    else
                    {
                        echo '<div class="alert alert-error"> <a class="close" data-dismiss="alert">×</a><strong>密码错误</strong></div>';
                    }
                }
            }
            else
            {
                echo '<div class="alert alert-error"> <a class="close" data-dismiss="alert">×</a><strong>用户名错误</strong></div>';
            }
        }
        catch(Exception $e)
        {
            echo '<div class="alert alert-error"> <a class="close" data-dismiss="alert">×</a><strong>用户名错误</strong></div>';
        }
    }
    ?>
    <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
</form>