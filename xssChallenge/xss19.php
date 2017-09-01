<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<script>

function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function checkCookie() {
    var user=getCookie("username");
    if (user != "") {
        document.write("欢迎, " + unescape(user));
    } else {
         alert("请登陆")
    }
}

</script>
</head>
<body onload="checkCookie()">
<?php echo '<img name="avatar" src="'.str_replace('"',"&quot;",$_GET["link"]).'" width="30" height="40">';?>
</body>
</html>