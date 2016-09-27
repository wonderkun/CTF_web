<?php

function pform() {
echo <<<CAT
  <form action="index.php?page=login" method="post">
    <p>用户名: <input type="text" name="name" /> </p> 
    <p><input type="submit" name="reset" value="忘记密码"></p>
    <p>密码: <input type="password" name="pass" /> </p>
    <p><input type="submit" name="login" value="登录"></p>
    <p>邮件: <input type="text" name="email" /> </p>
    <p><input type="submit" name="register" value="注册"></p>
  </form>
CAT;
}

mysql_connect("localhost","pic","bgddyrcv74%F");
mysql_select_db("pic");


if (isset($_SESSION['uid'])) {

  $q = mysql_query("select username from users where id=".$_SESSION['uid']);
  $res = mysql_fetch_object($q);
  echo sprintf("你已经登录过了哦!",$res->username);

}

else {

  if (isset($_POST["login"])) {
    $q = mysql_query(sprintf("select id,username from users where username='%s' and password='%s'",
      mysql_real_escape_string($_POST["name"]),mysql_real_escape_string($_POST["pass"])));
    $res = mysql_fetch_object($q);
    if (empty($res)) {
      echo "恩..用户名密码记错了吗";
      pform();
    }
    else {

      $_SESSION['uid'] = $res->id;
      echo sprintf("欢迎回来, %s!",$res->username);
    }
  }
  elseif (isset($_POST["register"])) {
    if (empty($_POST["name"]) or empty($_POST["email"]) or empty($_POST["pass"])) {
      echo "<p>所有选项都要填哦!</p>";
      pform();
    }
    else {
      $q = mysql_query(sprintf("insert into users (username,password,email) values
        ('%s', '%s', '%s')",mysql_real_escape_string($_POST["name"]),
        mysql_real_escape_string($_POST["pass"]),mysql_real_escape_string($_POST["email"])));
        if ($q) {
          echo "成功!";
        }
        else {
          echo sprintf("%s",mysql_error()); 
        }
    }
  }
  elseif (isset($_POST["reset"])) {
    $q = mysql_query(sprintf("select username,email,id from users where username='%s'",
      mysql_real_escape_string($_POST["name"]) ));
    $res = mysql_fetch_object($q);
    $passnew = "pic".bin2hex(openssl_random_pseudo_bytes(8));
    if ($res) {
      $ip = gethostbyaddr($_SERVER['REMOTE_ADDR']).
      mysql_query(sprintf("update users set password='%s', resetinfo='%s' where username='%s'",
              $passnew,$ip,$res->username)); //username 从数据库中读出来的，没有经过过滤，存在二次注入。
              
              //根据密码是否被改掉，判断是否为ture  

    }
    else {
      echo "这个用户好像没有注册";
    }

  }
  else {
    pform();
  }
}

?>