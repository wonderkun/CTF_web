<?php
if (isset($_POST["password"]) && isset($_POST["username"])) {
    $username = (string) $_POST["username"];
    $password = sha1($_POST["password"]);

    try {
        $user = new User($username, $password);
        $_SESSION["username"] = $username;
        $_SESSION["password"] = $password;
    } catch (Exception $e) {
        die("<script>alert('invalid user /password');</script>");
    }

    header("Location: /");
    die;
}
    


?>


<form action="/?page=login" method="POST">
<label for="username">Username</label>
<input name="username" id="username">
<label for="password">Password</label>
<input name="password" id="password">
<input type="submit" value="Login">
</form>

