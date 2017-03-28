<?php
session_start();
error_reporting(E_ALL^E_NOTICE^E_WARNING);
ini_set("session.cookie_httponly", 1);
$dbhost="127.0.0.1";
$dbuser="root";
$dbpassword="root";
$dbname="ctf";
$flag="BlueCTF{This_Is_Quite_Similar_To_ROP_And_You_Make_It!}";
global $conn;
$conn = mysqli_connect($dbhost,$dbuser,$dbpassword,$dbname);
if (mysqli_connect_errno())
{
  exit("Failed to connect to MySQL: " . mysqli_connect_error());
}
if(!isset($_GET['callback'])){
	$callback="";
}else{
	$callback=$_GET['callback'];
}
$callback=htmlentities($callback);

function myexit($callback,$msg){
	exit($callback."(\"".$msg."\")");
}
function sendMail($title,$content,$fromname,$toaddr,$username="15677960494@163.com",$password="xxxxx"){
	require_once './Mail/class.Mail.php'; 
	$m=new Mail();
    if( $m->send($title,$content,$fromname,$toaddr,$username,$password)){  
        echo "";  
    }else{  
        echo "".'<br>';  
        echo $m->$error;  
	} 
}
function random_str($length = "32")
{
	$seed = rand(0,999999999);
    mt_srand($seed);
    $set = array("a", "A", "b", "B", "c", "C", "d", "D", "e", "E", "f", "F",
        "g", "G", "h", "H", "i", "I", "j", "J", "k", "K", "l", "L",
        "m", "M", "n", "N", "o", "O", "p", "P", "q", "Q", "r", "R",
        "s", "S", "t", "T", "u", "U", "v", "V", "w", "W", "x", "X",
        "y", "Y", "z", "Z", "1", "2", "3", "4", "5", "6", "7", "8", "9");
    $str = '';

    for ($i = 1; $i <= $length; ++$i) {
        $ch = mt_rand(0, count($set) - 1);
        $str .= $set[$ch];
    }
    return $str;
}
function emailExist($email){
	global $conn;	
	$stmt = $conn->prepare('SELECT username FROM  user WHERE  email=? and verified=true');
	$stmt->bind_param('s', $email);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows>0){
		return true;
	}else{
		return false;
	}
}
function userExist($user){
	global $conn;	
	$stmt = $conn->prepare('SELECT username FROM  user WHERE  username=? and verified=true');
	$stmt->bind_param('s', $user);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows>0){
		return true;
	}else{
		return false;
	}
}
function getAdmin(){
	global $conn;	
	$stmt = $conn->prepare('SELECT username FROM  user WHERE  id=1 ');
	$stmt->execute();
	$result = $stmt->get_result();
	while ($row = $result->fetch_assoc()) {
		$admin=$row['username'];		
	}
	return $admin;
}
function isAdmin($username){
	global $conn;	
	$stmt = $conn->prepare('SELECT id FROM  user WHERE  id=1 and username=? ');
	$stmt->bind_param('s', $username);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows>0){
		return true;
	}else{
		return false;
	}
}
function sendmsg($from,$to,$msg){
	global $conn;
	if(!userExist($to)){
		myexit("alert","There is no username $to!");;
	}
	$stmt = $conn->prepare("INSERT into message(`from`,`to`,`msg`,`read`) VALUES (?,?,?,false)");	
	$stmt->bind_param('sss', $from,$to,$msg);
	$stmt->execute();
}

