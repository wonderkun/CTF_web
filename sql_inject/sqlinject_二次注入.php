<?php

include 'db.php';

session_start();
if (!isset($_SESSION['login'])){

  $_SESSION['login'] = 'guest'.mt_rand(1e5, 1e6);

  $login = $_SESSION['login'];
}   
if (isset($_POST['submit'])) {

  if (!isset($_POST['id'], $_POST['vote']) || !is_numeric($_POST['id']))
      die('please select ...');
  $id = $_POST['id'];

  $vote = (int)$_POST['vote'];

  if ($vote > 5 || $vote < 1)



    $vote = 1;

  $q = mysql_query("INSERT INTO t_vote VALUES ({$id}, {$vote}, '{$login}')");

  $q = mysql_query("SELECT id FROM t_vote WHERE user = '{$login}' GROUP BY id");
  
  echo '<p><b>Thank you!</b> Results:</p>';

  echo '<table border="1">';

  echo '<tr><th>Logo</th><th>Total votes</th><th>Average</th></tr>';

  while ($r = mysql_fetch_array($q)) {

      $arr = mysql_fetch_array(mysql_query("SELECT title FROM t_picture WHERE id = ".$r['id']));

      echo '<tr><td>'.$arr[0].'</td>';

      $arr = mysql_fetch_array(mysql_query("SELECT COUNT(value), AVG(value) FROM t_vote WHERE id = ".$r['id']));

      echo '<td>'.$arr[0].'</td><td>'.round($arr[1],2).'</td></tr>';

    }

    echo '<br><a href="index.php">goBack</a><br>';



    exit;

}

?>