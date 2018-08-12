function sleep(n) {
    var start = new Date().getTime();
    while (true) if (new Date().getTime() - start > n) break;
}

function login(){
    var username = $("#name").val();
    var password = $("#pass").val();
    $.ajax({
        url: '/login',
        type: 'POST',
        data: {'name': username,'pass': password},
        success:function(data) {
            result = data.result;
            if(result){
                var token = data.token;
                window.localStorage.setItem("token",token);
                window.location.href = "/user";
            }else{
                $('#login_error').html("login fail");
            }
        }
    });   
}

function reg(){
    var regname = $("#regname").val();
    var regpass = $("#regpass").val();
    $.ajax({
        url: '/reg',
        type: 'POST',
        data: {"regname": regname,"regpass":regpass},
    })
    .success(function(data) {
        result = data.result;
        if(result){
            alert("register success");
            window.location.href = "/";
        }else{
            $('#reg_error').html("register fail");
        }
    });
    
}

function getlist(){
    token = window.localStorage.getItem("token");
    if (token==null||token==undefined){
        alert("u must login first");
        window.location.href = "/";
        return;
    }
    auth = "Bearer " + token;
    $.ajax({
        url: '/list',
        type: 'GET',
        headers:{"Authorization":auth},
    })
    .success(function(data) {
        result = data.result;
        if(result){
            content = "the user " + data.username +" has these links:\n";
            for (var i in data.links){
                content = content + "/text/" + data.links[i] + "\n";
            } 
            alert(content);
        }else{
            alert("list fail");
        }
    });  
    
}

function paste(){
    var content = escape($("#content").val());
    token = window.localStorage.getItem("token");
    if (token==null||token==undefined){
        alert("u must login first");
        window.location.href = "/";
        return;
    }
    auth = "Bearer " + token;
    $.ajax({
        url: '/paste',
        type: 'POST',
        headers:{"Authorization":auth},
        data: {"content": content},
    })
    .success(function(data) {
        result = data.result;
        if(result){
            alert("u can open it with:" + "/text/" + data.link);
        }else{
            alert("paste fail");
        }
    });
    

}

function logout(){
    localStorage.clear();
    window.location.href = "/";
}

function getpubkey(){
    /* 
    get the pubkey for test
    /pubkey/{md5(username+password)}
    */
}