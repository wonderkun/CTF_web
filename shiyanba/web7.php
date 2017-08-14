
<?php

$flag = '{this_is_flag}'; 

if (isset($_GET['name']) and isset($_GET['password'])) {
    echo sha1($_GET['name'])."</br>";
    echo sha1($_GET['password'])."</br>";
      
    if ($_GET['name'] == $_GET['password'])
        echo '<p>Your password can not be your name!</p>';
         
    else if (sha1($_GET['name']) == sha1($_GET['password']))
      die('Flag: '.$flag);
    else
        echo '<p>Invalid password.</p>';
}
else{
	echo '<p>Login first!</p>';
}


?>

