<?php
// error_reporting(0);
$flag = "{this_is_flag}";

if (isset ($_GET['password'])) {
     
	if (ereg ("^[a-zA-Z0-9]+$", $_GET['password']) === FALSE)
	{
		echo '<p>You password must be alphanumeric</p>';
	
    }
	  else if (strlen($_GET['password']) < 8 && $_GET['password'] > 9999999)
	{    
    
		if (strpos ($_GET['password'], '*-*') !== FALSE)
		{
			die('Flag: ' . $flag);
		}
		else
		{
			echo('<p>*-* have not been found</p>');
		}
	}
	else
	{
		echo '<p>Invalid password</p>';
	}
}
?>
