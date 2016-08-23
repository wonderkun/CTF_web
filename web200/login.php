<html>
<meta charset="utf-8">
<head>
<!-- 新 Bootstrap 核心 CSS 文件 -->
<link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">

<!-- 可选的Bootstrap主题文件（一般不用引入） -->
<link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">

<!-- jQuery文件。务必在bootstrap.min.js 之前引入 -->
<script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>

<!-- 最新的 Bootstrap 核心 JavaScript 文件 -->
<script src="//cdn.bootcss.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

<title>登陆</title>
<style type="text/css">
	.main{
		width: 800px;
		margin: 0 auto; 
		margin-top: 200px;

	}
</style>
</head>
<body>
    
<div class="main">

<?php

include_once 'config.php'; 

function addflash($content){
    
    $replace="";
    $reg=array('/\|/','/--/','/or/i','/union/i','/#/','/select/i','/\*/','/\//');
    
    while (TRUE){
        
       $flag=false;
       foreach ($reg as $key => $value) {
        # code... 
        if (preg_match($value,$content)){
            
            $flag=true; 
            $content=preg_replace($value,$replace,$content);
        }
      }
            
    if(!$flag){
          break;
       }  
    }
    return $content;
    
}

if(isset($_POST['username'])&&isset($_POST['password'])){
    
	$count=0;    
    $username=$_POST['username'];  
    $password=$_POST['password'];
    $username=addflash($username);
    $password=addflash($password);
    
    $sql="select * from sqlinject where  username='$username' and  password='$password' ";
    // echo $sql;
    
?>
<table class="table">
   <caption>hint：<?php echo "</br>"."username:".$username."</br>";  echo "password:".$password; ?></caption>
   <thead>
      <tr>
         <th>username</th>
         <th>password</th>
      </tr>
   </thead>
   <tbody>
<?php 

	//echo $username;
    $result=mysql_query($sql,$conn);
    //echo mysql_error();
    if($result){
      while ($row=mysql_fetch_array($result)) {
    	echo "<tr><td>".$row['username']."</td>";
        echo "<td>".$row['password']."</td></tr>";

    	$count=$count+1;
    	# code...
     }	
    }
    
   if($count<3){
     echo "对不起，没有此用户！！";

   }else{

   	echo "ctf{".$flag."}"; 

   }

}

?>

</tbody>
</table>
</div>
</body>
</html>

