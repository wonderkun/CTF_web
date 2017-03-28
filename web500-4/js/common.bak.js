//a=1
function callback(errmsg){
		$("#result").html(errmsg);
}
function welcome(username){
		$("#name").html(username);
}
function displaymsg(msg){
		$("#msg_body").append(msg);
}
function stop(){
	return false;
}
function refresh(seconds){
	setInterval("location.href=location.href",seconds*1000);
}
$(document).ready(function(){
	$("#login").click(function(){
		var username=$('#login_username').val();
		var pwd=$('#login_password').val();
		if(!(username.length && pwd.length)){
			legal= false;
		}else{
			legal=true;
		}
		if(!legal){
			alert("Please check that all fields CANNOT be empty!");
			return false;			
		}
		$.ajax({
			url: "login.php?callback=callback&username="+username+"&password="+pwd,
			dataType: "script",
			success: null
		});		
	});	
	$("#register").click(function(){
		var username=$('#username').val();
		var pwd=$('#password').val();
		var email=$('#email').val();	
		if(!(username.length && pwd.length && email.length)){ 
			legal= false;
		}else{
			legal=true;
		}
		if(!legal){
			alert("Please check that all fields CANNOT be empty!");
			return false;
		}
		$.ajax({
			url: "register.php?callback=callback&username="+username+"&password="+pwd+"&email="+email,
			dataType: "script",
			success: null
		});		
	});
	$("#resetpwd").click(function(){
		var username=$('#reset_username').val();	
		if(!(username.length)){ 
			legal= false;
		}else{
			legal=true;
		}
		if(!legal){
			alert("Please check that username CANNOT be empty!");
			return false;
		}
		$.ajax({
			url: "resetpwd.php?callback=callback&username="+username,
			dataType: "script",
			success: null
		});		
	});	
	$("#resetpwd1").click(function(){
		var username=$('#reset_username').val();	
		var newpwd=$('#newpwd').val();		
		if(!(username.length && newpwd.length)){ 
			legal= false;
		}else{
			legal=true;
		}
		if(!legal){
			alert("Please check that all fields CANNOT be empty!");
			return false;
		}
		$.ajax({
			url: "resetpwd.php?callback=callback&username="+username+"&newpwd="+newpwd,
			dataType: "script",
			success: null
		});		
	});	
	$("#sendmsg").click(function(){
		var touser=$('#touser').val();	
		var msg=$('#msg').val();		
		if(!(touser.length && msg.length)){ 
			legal= false;
		}else{
			legal=true;
		}
		if(!legal){
			alert("Please check that all fields CANNOT be empty!");
			return false;
		}
		$.ajax({
			url: "sendmsg.php?callback=alert&touser="+touser+"&msg="+msg,
			dataType: "script",
			success: null
		});		
	});
	$("#advise").click(function(){	
		var msg=$('#msg').val();		
		if(!( msg.length)){ 
			legal= false;
		}else{
			legal=true;
		}
		if(!legal){
			alert("Please check that msg  CANNOT be empty!");
			return false;
		}
		$.ajax({
			url: "advise.php?callback=alert&msg="+msg,
			dataType: "script",
			success: null
		});		
	});	
	$("#username").blur(function(){
		var username=$('#username').val();	
		$.ajax({
			url: "check.php?callback=callback&username="+username,
			dataType: "script",
			success: null
		});
	});
	
	$("#email").blur(function(){
		var email=$('#email').val();	
		$.ajax({
			url: "check.php?callback=callback&email="+email,
			dataType: "script",
			success: null
		});
	});
	(function(){
		if($('#name').length){
			$.ajax({
				url: "check.php?callback=welcome",
				dataType: "script",
				success: null
			});
		}
		if($('#msg_body').length){
			setInterval(
				(function(){
					$.ajax({
						url: "showmsg.php?callback=displaymsg",
						dataType: "script",
						success: null
					})
				})
			,100);
		}
		if(window.event && window.event.keyCode == 123) {
			window.event.returnValue=false;
			return false; 
		}
		$('html').css({  
                cursor: 'none'  
        }); 
		(function preventDefault(event){
			event = event || window.event;
			event.preventDefault ? event.preventDefault() : event.returnValue = false;
		})(null)
		document.oncontextmenu=stop;
	})();	
});