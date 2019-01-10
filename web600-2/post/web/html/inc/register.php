<?php

if (isset($_POST["password"]) && isset($_POST["username"])) {
    $username = (string) $_POST["username"];
    $password = sha1($_POST["password"]);
    if (strlen($username) < 6) 
        die("<script>alert('username too short');</script>");

    try {
        User::create($username, $password);
    } catch (Exception $e) {
        die("<script>alert('username exists');</script>");
    }

    header("Location: /?page=login");
    die;
}
    


?>


<form action="/?page=register" method="POST">
<label for="username">Username</label>
<input name="username" id="username">
<label for="password">Password</label>
<input name="password" id="password">
<input type="submit" value="Register">
</form>

