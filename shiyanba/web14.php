
<?php

//sql注入绕过, 

error_reporting(0);

if (!isset($_POST['uname']) || !isset($_POST['pwd'])) {
	echo '<form action="" method="post">'."<br/>";
	echo '<input name="uname" type="text"/>'."<br/>";
	echo '<input name="pwd" type="text"/>'."<br/>";
	echo '<input type="submit" />'."<br/>";
	echo '</form>'."<br/>";
	echo '<!--source: source.txt-->'."<br/>";
    die;
}

function AttackFilter($StrKey,$StrValue,$ArrReq){  
    if (is_array($StrValue)){
        $StrValue=implode($StrValue);
    }
    if (preg_match("/".$ArrReq."/is",$StrValue)==1){   
        print "水可载舟，亦可赛艇！";
        exit();
    }
    
}

$filter = "and|select|from|where|union|join|sleep|benchmark|,|\(|\)|like|rlike|regxp";
foreach($_POST as $key=>$value){ 
    AttackFilter($key,$value,$filter);
}

$con = mysql_connect("localhost","root","iove*ctf");
if (!$con){
	die('Could not connect: ' . mysql_error());
}
$db="npuctf";

mysql_select_db($db, $con);
$sql="SELECT * FROM interest WHERE uname = '{$_POST['uname']}'";
$query = mysql_query($sql); 
if (mysql_num_rows($query) == 1) { 
    $key = mysql_fetch_array($query);
    if($key['pwd'] == $_POST['pwd']) {
        print "CTF{XXXXXX}";
    }else{
        print "亦可赛艇！";
    }
}else{
	print "一颗赛艇！";
}
mysql_close($con);

?>


<!--/select * from admin where 1=1  group by pwd with rollup limit 1 offset 2 --> 
    