

var rmd_sta = 0;





//$(function () {

//    $(document.body).append('<div class="nin_arrow" style="display:none"></div>');

//    $(document.body).append('<div class="a4_1" style="display:none" >为我推荐</div>');

//    $(document.body).append('<div class="nin" style="display:none"></div>');

//    var html = "";

//    html += '<div class="nin_top"></div>';

//    html += '<div class="nin_center">';

//    html += '<div class="nin_title"><span></span><a href="javascript:;" onclick="close_my_rmd()" hidefocus="hidefocus"></a></div>';

//    html += '<div class="nin_txt_loading">';

//    html += '稍等，正在分析您的喜好，慌吗？';

//    html += '</div>';

//    html += '<div class="nin_txt">';

//    html += '<div class="ner1">';

//    html += '<ul id="rmd_article1">';

//    html += '</ul>';

//    html += '<ul id="rmd_article2" class="no" >';

//    html += '</ul>';

//    html += '</div>';

//    html += '<div class="ner2">';

//    html += '<ul id="rmd_pic">';

//    html += '</ul>';

//    html += '</div>';

//    html += '</div>';

//    html += '</div>';

//    html += '<div class="nin_bottom"></div>';

//    $(".nin").html(html);



//});

$(function () {



    var html = "";

    html += '<div class="sd_main">';

    html += '<div class="top">';

    html += '<div class="bg-1"></div>';

    html += '<div class="bg-2"></div>';

    html += '<div class="bg-3"></div>';

    html += '<div class="bg-4"></div>';

    html += '<div class="bg-5"></div>';

    html += '<div class="bg-6"></div>';

    html += '<div class="bg-7"></div>';

    html += '<div class="bg-8"></div>';

    html += '<div class="bg-9"></div>';

    html += '</div>';

    html += '<div class="sd_cen">';

    html += '<div class="txt">';

    html += '<div class="cen1">';

    html += '<div class="img"></div>';

    html += '<div class="item">';

    html += '<ul></ul>';

    html += '</div>';

    html += '</div>';

    html += '<div class="cen1">';

    html += '<div class="img"></div>';

    html += '<div class="item">';

    html += '<ul></ul>';

    html += '</div>';

    html += '</div>';

    html += '<div class="cen1">';

    html += '<div class="img"></div>';

   // html += '<div class="item">';

    html += '<ul></ul>';

    html += '</div>';

    html += '</div>';

    html += '</div>'; //txt end

  

    html += '</div>'; //sd_cen end

    html += '</div>'; //sd_main end

    $(document.body).append(html);







});

 







function my_rmd() {

    $(document.body).append('<div class="screen_msg" id="screen_msg"></div>');

    $(".screen_msg").height($(window).height());



    $("embed").each(function () {

        if ($(this).css("display") == "inline") {

            $(this).attr("rel", "holder");

            $(this).hide();

        }

    });

    $("#screen_msg").css("background", "#000");

    $("#screen_msg").css("opacity", "0");



    var ti = setTimeout(function () {

        $(".sd_main").css("left", ($(document).width() - $(".sd_main").width()) / 2 + "px");

        $(".sd_main").show();

        var a4_left = $(".a4").offset().left;

        $(".a4_1").css("left", a4_left + "px");

        $(".a4_1").css("opacity", "1");

        $(".a4_1").show();

        clearTimeout(ti);

    }, 500);

    $("#screen_msg").animate({ opacity: '0.5' }, 1000);

    var ie = ietester();

    if (ie <= 6) {

        $(window).unbind("scroll");

        $(window).scroll(function () {

            $("#screen_msg").css("top", $(window).scrollTop() + "px");

        });

    }

    var imgs = "";

    var articles1 = "";

    var articles2 = "";

    var articles3 = "";

    if (rmd_sta > 0) {

        $(".sd_main").css("top", "15px");

     

        $(".sd_main").css("left", ($(document).width() - $(".sd_main").width()) / 2 + "px");

        return;

    }

    $.getScript("http://www.7y7.com/ajaxHandler/Handler.ashx?action=recmd2user1&r=" + Math.random(), function () {

        var data = eval(recmd2user_json);

        var img_t = 0;

        var article_t = 0;

        for (i = 0; i < data.length; i++) {

            var href = ParseArticlePath(data[i].id, data[i].rootpath, data[i].isSubDomain1, data[i].isSubDomain2);

            if (data[i].t == "article") {

                if (article_t < 5)

                    articles1 += '<li><a href="' + href + '" target="_blank"  class="txt1" title="' + data[i].head + '">' + data[i].head_short + '</a></li>';

                else if (article_t < 10)

                    articles2 += '<li><a href="' + href + '" target="_blank"  class="txt1" title="' + data[i].head + '">' + data[i].head_short + '</a></li>';

                else if (article_t < 15)

                    articles3 += '<li><a href="' + href + '" target="_blank"  class="txt1" title="' + data[i].head + '">' + data[i].head_short + '</a></li>';

                article_t++;

            }

            else {

                //imgs += '<li><a href="http://www.7y7.com/pic/' + getPath(data[i].id) + '" target="_blank"><img src="http://pic.7y7.com/' + ResizeImage(data[i].pic, "120x160") + '" width="120" height="160" />' + data[i].head + '</a></li>';

                if (img_t < 3)

                    $(".sd_cen .txt .cen1:eq(" + img_t + ") .img").html('<a href="http://www.7y7.com/pic/' + getPath(data[i].id) + '" target="_blank" title="' + data[i].head + '"><img src="http://pic.7y7.com/' + ResizeImage(data[i].pic, "120x160") + '"  /></a>');

                img_t++;

            }

        }

        $(".sd_cen .txt .cen1:eq(0)").css("margin-left", "10px");

        $(".sd_cen .txt .cen1:eq(0) ul").html(articles1);

        $(".sd_cen .txt .cen1:eq(1) ul").html(articles2);

       // $(".sd_cen .txt .cen1:eq(2) ul").html(articles3);

        //$(".sd_cen .imgtxt ul").html(imgs);

        rmd_sta = 1;

    });



}





function close_my_rmd() {

    $(window).unbind("scroll");

    var left = $(".a4").offset().left; 

    $(".sd_main").animate({ left: (left + 5) + "px", top: "1px", height: "30px", width: "80px" }, 500, function () {

        $(".sd_main").attr("style", "");

        $(".sd_main").hide();

        $(".a4_1").css("background-color", "#FF0066");

        $(".a4_1").css("color", "#fafafa");

        var timeout = setTimeout(function () {

            $(".a4_1").css("background-color", "#fafafa");

            $(".a4_1").css("color", "#444444");

            $(".a4_1").hide();

            clearTimeout(timeout);

        }, 200);

    });

    $("#screen_msg").remove();

    $("embed[rel='holder']").each(function () {

        $(this).show();

    });

}



function ietester() {

    var undef,

		ie,

		v = 3,

		div = document.createElement('div'),

		all = div.getElementsByTagName('i');

    while (

			div.innerHTML = '<!--[if gt IE ' + (++v) + ']><i></i><![endif]-->',

			all[0]

		);

    v > 4 ? ie = v : ie = undef;

    return ie;

}

//function close_my_rmd() {

//    $(window).unbind("scroll");

//    var left = $(".a4").offset().left;

//    $(".nin_arrow").hide();

//    $(".nin").animate({ left: (left + 5) + "px", top: "1px", height: "30px", width: "80px" }, 500, function () {

//        $(".nin").attr("style", "");

//        $(".nin").hide();

//        $(".a4_1").css("background-color", "#FF0066");

//        $(".a4_1").css("color", "#fafafa");

//        var timeout = setTimeout(function () {

//            $(".a4_1").css("background-color", "#fafafa");

//            $(".a4_1").css("color", "#444444");

//            $(".a4_1").hide();

//            clearTimeout(timeout);

//        }, 200);

//    });

//    $("#screen_msg").remove();

//    $("embed[rel='holder']").each(function () {

//        $(this).show();

//    });

//}

//function my_rmd() {



//    $(document.body).append('<div class="screen_msg" id="screen_msg"></div>');



//    $(".screen_msg").height($(window).height());



//     

//    $("embed").each(function () {

//        if ($(this).css("display") == "inline") {

//            $(this).attr("rel", "holder");

//            $(this).hide();

//        }

//    });

//    $("#screen_msg").css("background", "#000");

//    $("#screen_msg").css("opacity", "0");



//    var ti = setTimeout(function () {

//        $(".nin_arrow").show();

//        $(".nin").css("left", ($(document).width() - $(".nin").width()) / 2 + "px");

//        $(".nin").show();

//        var a4_left = $(".a4").offset().left;

//        $(".a4_1").css("left", a4_left + "px");

//        $(".a4_1").css("opacity", "1");

//        $(".a4_1").show();

//        $(".nin_arrow").css("left", ($(document).width() - $(".nin").width()) / 2 + 635 + "px");

//        clearTimeout(ti);

//    }, 500);



//    $("#screen_msg").animate({ opacity: '0.5' }, 1000);

//    var ie = ietester();

//  

//    if (ie && ie <= 6) {

//        $(window).unbind("scroll");

//        $(window).scroll(function () {

//            $("#screen_msg").css("top", $(window).scrollTop() + "px");

//        });

//    }

//  

//    var imgs = "";

//    var articles1 = "";

//    var articles2 = "";

//    if (rmd_sta > 0) {

//        $(".nin").css("top", "165px");

//        $(".nin").css("height", "437px");

//        $(".nin").css("width", "644px");

//        $(".nin").css("left", ($(document).width() - $(".nin").width()) / 2 + "px");

//        return;

//    }

//   

//    $.getScript("http://www.7y7.com/ajaxHandler/Handler.ashx?action=recmd2user&r=" + Math.random(), function () {

//        var data = eval(recmd2user_json);

//        var img_t = 0;

//        var article_t = 0;



//        for (i = 0; i < data.length; i++) {

//            var href = ParseArticlePath(data[i].id, data[i].rootpath, data[i].isSubDomain1, data[i].isSubDomain2);

//            if (data[i].t == "article") {

//                if (article_t % 2 == 0)

//                    articles1 += '<li><a href="' + href + '" target="_blank">' + data[i].head + '</a></li>';

//                else

//                    articles2 += '<li><a href="' + href + '" target="_blank">' + data[i].head + '</a></li>';

//                article_t++;

//            }

//            else {

//                if (img_t == 3)

//                    imgs += '<li class="no1"><a href="http://www.7y7.com/pic' + '/' + getPath(data[i].id) + '" target="_blank"><img src="http://pic.7y7.com/' + ResizeImage(data[i].pic, "120x160") + '" width="120" height="160" />' + data[i].head + '</a></li>';

//                else

//                    imgs += '<li><a href="http://www.7y7.com/pic' + '/' + getPath(data[i].id) + '" target="_blank"><img src="http://pic.7y7.com/' + ResizeImage(data[i].pic, "120x160") + '" width="120" height="160" />' + data[i].head + '</a></li>';

//                img_t++;

//            }

//        }

//        $(".ner1 #rmd_article1").html(articles1);

//        $(".ner1 #rmd_article2").html(articles2);

//        $(".ner2 #rmd_pic").html(imgs);

//        $(".nin_txt_loading").hide();

//        $(".nin_txt").show();

//        rmd_sta = 1;

//    });

//    

//}



function ResizeImage(src, size) {

    var ret = "";

    try {

        ret = src.split('.')[0] + "_" + size + "." + src.split('.')[1];

    }

    catch (e) { }

    return ret;

}

 

$(function () {

    $("#qzone").hide();

    if ($("#qzone").length > 0) {

        var st = setTimeout(function () {

            $("#qzone").attr("src", "http://open.qzone.qq.com/like?url=http%3A%2F%2Fuser.qzone.qq.com%2F19881012&type=button_num&width=400&height=30");

            clearTimeout(st);

            $("#qzone").show();

        }, 10000);

    }

    $(document.forms["so_form"]).submit(function () { goSearch(document.forms["so_form"]); return false; });

});

function goSearch() {

    var tO = document.forms["so_form"];

    var tStr = tO.t_keywords.value;

    if (!tStr) return alert('请填写搜索关键字！');

    tStr = encodeURI("" + tStr);

    window.open('http://so.7y7.com/cse/search?q=' + tStr + '&s=5364651094490175667');

    return false;

}

function SetCookie(value, name, key) {

    var Days = 2;

    var exp = new Date();

    var domain = "7y7.com";

    exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);

    if (key == null || key == "") {

        document.cookie = name + "=" + encodeURI(value) + ";expires=" + exp.toGMTString() + ";path=/;domain=" + domain + ";";

    }

    else {

        var nameValue = getCookie(name);

        if (nameValue == "") {

            document.cookie = name + "=" + key + "=" + encodeURI(value) + ";expires=" + exp.toGMTString() + ";path=/;domain=" + domain + ";";

        }

        else {

            var keyValue = getCookie(name, key);

            if (keyValue != "") {

                nameValue = nameValue.replace(key + "=" + keyValue, key + "=" + encodeURI(value));

                document.cookie = name + "=" + nameValue + ";expires=" + exp.toGMTString() + ";path=/;domain=" + domain + ";";

            }

            else {

                document.cookie = name + "=" + nameValue + "&" + key + "=" + encodeURI(value) + ";expires=" + exp.toGMTString() + ";path=/;" + domain + ";";

            }

        }

    }

}



function GetCookie(name, key) {

    var nameValue = "";

    var arr, reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)");

    if (arr = document.cookie.match(reg)) {

        nameValue = decodeURI(arr[2]);

    }

    if (key != null && key != "") {

        reg = new RegExp("(^| |&)" + key + "=([^(;|&|=)]*)(&|$)");

        if (arr = nameValue.match(reg)) {

            return decodeURI(arr[2]);

        }

        else return "";

    }

    else {

        return nameValue;

    }

}



function KillCookie(name) {

    var exp = new Date();

    exp.setTime(exp.getTime() - 1);

    var cval = getCookie(name);



    if (cval != null) document.cookie = name + "=" + cval + ";expires=" + exp.toGMTString() + ";domain=7y7.com";

}





function shareour() {

    if ($("#share").length > 0) {

        $("#share").show();

        return;

    }

    $(document.body).append("<div id=\"share\" style='display:none'>");

    $("#share").css("top", "100px");

    var window_w = $(window).width();

    var obj_w = $("#share").width();

    $("#share").css("left", (window_w - obj_w) / 2 + "px");



    $("#share").append("<div class=\"share_title\">");



    $("#share .share_title").html("<span class=\"share_title1\">推荐给您的好朋友</span><i class=\"close\"></i>");

    $("#share .share_title .close").click(function () {

        $("#share").fadeOut(100);

    });

    var title = document.title;

    $("#share").append("<div class=\"share_msg\">");

    $("#share .share_msg").html("Hi，感谢您浏览七丽女性网，如果您觉得我们网站不错的话，可以点击下方按钮直接分享给您的好友。");

    var obj = $("#jiathis_style_32x32");

    $("#jiathis_style_32x32").remove();





    $("#share").fadeIn(100, function () {

        $("#share").append(obj);

        $("#share #jiathis_style_32x32").show();

        $("#share #jiathis_style_32x32").css("padding", "10px");

        $("#share #jiathis_style_32x32").css("clear", "both");

        $("#share .jiathis").css("font-size", "16px");

        $("#share .jiathis").css("font-family", "Microsoft Yahei");

    });

}

function copycilp(txt) {

    txt = txt.replace(/<br>/g, "\r\n")

    if (window.clipboardData) {

        window.clipboardData.clearData();

        window.clipboardData.setData("Text", txt);

        return true;

    } else if (navigator.userAgent.indexOf("Opera") != -1) {

        window.location = txt;

        return true;

    } else if (window.netscape) {

        try {

            netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

        } catch (e) {

            alert("被浏览器拒绝！\n请在浏览器地址栏输入'about:config'并回车\n然后将'signed.applets.codebase_principal_support'设置为'true'");

            return false;

        }

        var clip = Components.classes['@mozilla.org/widget/clipboard;1'].createInstance(Components.interfaces.nsIClipboard);

        if (!clip)

            return false;

        var trans = Components.classes['@mozilla.org/widget/transferable;1'].createInstance(Components.interfaces.nsITransferable);

        if (!trans)

            return false;

        trans.addDataFlavor('text/unicode');

        var str = new Object();

        var len = new Object();

        var str = Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString);

        var copytext = txt;

        str.data = copytext;

        trans.setTransferData("text/unicode", str, copytext.length * 2);

        var clipid = Components.interfaces.nsIClipboard;

        if (!clip)

            return false;

        clip.setData(trans, null, clipid.kGlobalClipboard);

        return true;

    }



}

function resetimgsize(img, width) {



    if (img.width > width) {

        img.width = width;



        $(img).css("margin", "0 auto");

    }

}

function getPath(id) {

    id = "" + id;

    var len = id.split("").length;

    return ((len > 1) ? id.substr(len - 2, len) : "0" + id) + "/" + id + ".html";

}





function ParseArticlePath(id, rootpath, isSubDomain1, isSubDomain2) {

    var url = "";

    url = "http://www.7y7.com/" + rootpath + "/" + getPath(id);

    return url;

}

function ParseRootPath(id, rootpath, isSubDomain1) {

    var url = "";

    if (isSubDomain1 > 0) {

        url = "http://" + rootpath + ".7y7.com/";

    }

    else {

        url = "http://www.7y7.com/" + rootpath + "/";

    }

    return url;

}

function ParseSubRootPath(id, rootpath, isSubDomain1, isSubDomain2) {

    var url = "";

    if (isSubDomain2 > 0) {

        url = "http://" + rootpath + ".7y7.com/";

    }

    else {

        url = "http://www.7y7.com/" + rootpath + "/";

    }

    return url;

}



function fontsize(size) {

    $(".jies").css("font-size", size + "px");

    $(".li_daodu").css("font-size", size + "px");

}



function openQQ() {

    var A = window.open("http://www.7y7.com/api/logintoqq.aspx");

}



function openUrl(url) {

    var A = window.open(url);

}

function AddFavorite(sURL, sTitle) {

    try {

        window.external.addFavorite(sURL, sTitle);

    }

    catch (e) {

        try {

            window.sidebar.addPanel(sTitle, sURL, "");

        }

        catch (e) {

            alert("加入收藏失败，请使用Ctrl+D进行添加");

        }

    }

}

function SetHome(obj, vrl) {

    try {

        obj.style.behavior = 'url(#default#homepage)'; obj.setHomePage(vrl);

    }

    catch (e) {

        if (window.netscape) {

            try {

                netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

            }

            catch (e) {

                alert("此操作被浏览器拒绝！\n请在浏览器地址栏输入“about:config”并回车\n然后将 [signed.applets.codebase_principal_support]的值设置为'true',双击即可。");

            }

            var prefs = Components.classes['@mozilla.org/preferences-service;1'].getService(Components.interfaces.nsIPrefBranch);

            prefs.setCharPref('browser.startup.homepage', vrl);

        }

    }

}

String.prototype.Trim = function () {

    return this.replace(/^\s+/g, "").replace(/\s+$/g, "");

}

String.prototype.DelBr = function () {

    return this.replace(/\r\n/g, "");

}



function delHtmlTag(str) {

    return str.replace(/<[^>]+>/g, "");  

}





function byteLength(sStr) {



    aMatch = sStr.match(/[^\x00-\x80]/g);

    return (sStr.length + (!aMatch ? 0 : aMatch.length));

}





function changeMaxLen(obj, len1, location) {



    var len = len1;

    var num = 0;

    var strlen = 0;

    var obj_value_arr = obj.value.split("");

    for (var i = 0; i < obj_value_arr.length; i++) {

        if (i < len && num + byteLength(obj_value_arr[i]) <= len) {

            num += byteLength(obj_value_arr[i]);

            strlen = i + 1;

        }

    }

    if (obj.value.length > strlen) {

        obj.value = obj.value.substr(0, strlen);

    }

    var lenText = document.getElementById('lenSpan' + location);

    lenText.innerHTML = len - byteLength(obj.value);



}



function getPhotoPath(type) {

    var str = "";

    switch (type) {

        case "明星写真":

            str = "xiezhen";

            break;

        case "清纯美女":

            str = "meinv";

            break;

        case "时尚大片":

            str = "dapian";

            break;

        case "性感尤物":

            str = "youwu";

            break;

        default: str = "";

            break;

    }

    return str;

}



function AddFavorite(sURL, sTitle) { try { window.external.addFavorite(sURL, sTitle); } catch (e) { try { window.sidebar.addPanel(sTitle, sURL, ""); } catch (e) { alert("加入收藏失败，请使用Ctrl+D进行添加"); } } }

