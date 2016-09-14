<?php

mysql_connect("localhost","pic","bgddyrcv74%F");
mysql_select_db("pic");


$q = mysql_query("select pid,path from pictures");
while ($res = mysql_fetch_object($q)) {
  $id = $res->pid;
  $path = $res->path;
  echo "<figure class='annotated'>";
  echo "<img src='imgs/$path'>";
  echo "</figure>";
}

?>