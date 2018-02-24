<?php
include 'config.php';
error_reporting(0);
define("SECRET_KEY", "this_is_key_you_do_not_know");
define("METHOD", "aes-128-cbc");

session_start();

function get_random_token(){
    $random_token='';
    for($i=0;$i<16;$i++){
        $random_token.=chr(rand(1,255));
    }
    return $random_token;
}

function get_identity()
{
    global $defaultId;
    $j = $defaultId;
    $token = get_random_token();
    $c = openssl_encrypt($j, METHOD, SECRET_KEY, OPENSSL_RAW_DATA, $token);
    $_SESSION['id'] = base64_encode($c);
    setcookie("ID", base64_encode($c));
    setcookie("token", base64_encode($token));
    if ($j === 'admin') {
        $_SESSION['isadmin'] = true;
    } else $_SESSION['isadmin'] = false;

}

function test_identity()
{
    if (!isset($_COOKIE["token"]))
        return array();
    if (isset($_SESSION['id'])) {
        $c = base64_decode($_SESSION['id']);
        if ($u = openssl_decrypt($c, METHOD, SECRET_KEY, OPENSSL_RAW_DATA, base64_decode($_COOKIE["token"]))) {
            if ($u === 'admin') {
                $_SESSION['isadmin'] = true;
            } else $_SESSION['isadmin'] = false;
        } else {
            die("ERROR!");
        }
    }
}

function login($encrypted_pass, $pass)
{
    $encrypted_pass = base64_decode($encrypted_pass);
    $iv = substr($encrypted_pass, 0, 16);
    $cipher = substr($encrypted_pass, 16);
    $password = openssl_decrypt($cipher, METHOD, SECRET_KEY, OPENSSL_RAW_DATA, $iv);
    return $password == $pass;
}



function need_login($message = NULL) {
    echo "   <!doctype html>
        <html>
        <head>
        <meta charset=\"UTF-8\">
        <title>Login</title>
        <link rel=\"stylesheet\" href=\"CSS/target.css\">
            <script src=\"https://cdnjs.cloudflare.com/ajax/libs/prefixfree/1.0.7/prefixfree.min.js\"></script>
        </head>
        <body>";
    if (isset($message)) {
        echo "  <div>" . $message . "</div>\n";
    }
    echo "<form method=\"POST\" action=''>
            <div class=\"body\"></div>
                <div class=\"grad\"></div>
                    <div class=\"header\">
                        <div>Log<span>In</span></div>
                    </div>
                    <br>
                    <div class=\"login\">
                        <input type=\"text\" placeholder=\"username\" name=\"username\">
                        <input type=\"password\" placeholder=\"password\" name=\"password\">               
                        <input type=\"submit\" value=\"Login\">
                    </div>
                     <script src='http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
            </form>
        </body>
    </html>";
}

function show_homepage() {
    echo "<!doctype html>
<html>
<head><title>Login</title></head>
<body>";
    global $flag;
    printf("Hello ~~~ ctfer! ");
    if ($_SESSION["isadmin"])
        echo $flag;
    echo "<div><a href=\"logout.php\">Log out</a></div>
</body>
</html>";

}

if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = (string)$_POST['username'];
    $password = (string)$_POST['password'];
    $query = "SELECT username, encrypted_pass from users WHERE username='$username'";
    $res = $conn->query($query) or trigger_error($conn->error . "[$query]");
    if ($row = $res->fetch_assoc()) {
        $uname = $row['username'];
        $encrypted_pass = $row["encrypted_pass"];
    }
    
    if ($row && login($encrypted_pass, $password)) {
        echo "you are in!" . "</br>";
        get_identity();
        show_homepage();
    } else {
        echo "<script>alert('login failed!');</script>";
        need_login("Login Failed!");
    }

} else {
    test_identity();
    if (isset($_SESSION["id"])) {
        show_homepage();
    } else {
        need_login();
    }
}