function GetRequest() {   
   var url = location.search;
   var theRequest = new Object();   
   if (url.indexOf("?") != -1) {   
      var str = url.substr(1);   
      strs = str.split("&");   
      for(var i = 0; i < strs.length; i ++) {   
         theRequest[strs[i].split("=")[0]]=unescape(strs[i].split("=")[1]);   
      }   
   }   
   return theRequest;   
}

function xmlhttp() {
        var xhr = new XMLHttpRequest();
        xhr.open("post", "http://124.16.75.161:40002/repost.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded;");
        xhr.onreadystatechange = function() {
            console.log(xhr.responseText);
            if (xhr.readyState == 4 && xhr.status == 200) {
                console.log(xhr.responseText);
            }
        };
        var url_query = GetRequest()

        var content = "expr="+url_query['expr']+"&vars="+url_query['vars'];
        xhr.send(content);
    }

