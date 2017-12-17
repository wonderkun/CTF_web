<?php 

# 管理员API：adminApi/index.php

error_reporting(0);
function isInternalIp($ip) {

    $ip = ip2long($ip);
    $net_a = ip2long('10.255.255.255') >> 24; //A类网预留ip的网络地址
    $net_b = ip2long('172.31.255.255') >> 20; //B类网预留ip的网络地址
    $net_c = ip2long('192.168.255.255') >> 16; //C类网预留ip的网络地址
    $net_l = ip2long('127.255.255.255') >> 24;
    return $ip >> 24 === $net_a || $ip >> 20 === $net_b || $ip >> 16 === $net_c || $ip >> 24 === $net_l;
}

$rawData = file_get_contents("php://input");
$data = json_decode($rawData,true);
$response = array();

if(!$data){
    $response["state"] = "FAIL";
    $response["message"] = "post data error!";
    echo json_encode($response);
    die();
}
// var_dump($data);

$ch = curl_init();
$url = urldecode($data["url"]);
$urlInfo = parse_url($url);

//判断协议
if(!("http" === strtolower($urlInfo["scheme"]) || "https"===strtolower($urlInfo["scheme"]))){
    $response["state"] = "FAIL";
    $response["message"] = "scheme error!";
    echo json_encode($response,true);
    die();
}

//过滤SSRF

// if(preg_match('/^([2][0-4]\d|[2][5][0-5]|[1]\d\d|[1-9][\d]|[\d])(\.([2][0-4]\d|[2][5][0-5]|[1]\d\d|[1-9][\d]|[\d])){3}$/',$urlInfo["host"])){ 如果是这样有几种做法？
    
if(preg_match('/^([2][0-4]\d|[2][5][0-5]|[1]\d\d|[1-9][\d]|[\d])(\.([2][0-4]\d|[2][5][0-5]|[1]\d\d|[1-9][\d]|[\d])){3}/',$urlInfo["host"])){
    $ip = $urlInfo["host"];
}else{
    $ip = gethostbyname($urlInfo["host"]);    
}

if(isInternalIp($ip)){
   $response["state"] = "FAIL";
   $response["message"] = "SSRF attack is not allow!";
   die(json_encode($response));
}


// var_dump($urlInfo);

// die();
// var_dump($url);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch,CURLOPT_SAFE_UPLOAD,0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch,CURLINFO_HEADER_OUT,1);


switch(urldecode($data["type"])){
    case "POST" :
        curl_setopt($ch,CURLOPT_POST,1);
        $postData = $data["postData"];
        $postData = array_map("urldecode",$postData);
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);    
        array_push($data['header'],"Expect: ");
        // var_dump($postData);
    break;   
    case "GET":
        curl_setopt($ch,CURLOPT_POST,0);
        break;
    default:
        curl_setopt($ch,CURLOPT_POST,0);
}

// var_dump($data['header']);

$header = array_map("urldecode",$data['header']);
// var_dump($header);
curl_setopt($ch,CURLOPT_HTTPHEADER,$header);

$output = curl_exec($ch);

$reqHeader = curl_getinfo($ch,CURLINFO_HEADER_OUT);
$res =  $output;

$response["state"] = "SUCCESS";
$response["reqHeader"] = $reqHeader;
$response["res"] = $res;

echo json_encode($response);
