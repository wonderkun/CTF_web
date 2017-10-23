<?php 


$response = array();

if($_SERVER["REMOTE_ADDR"]!=="127.0.0.1"){
     $response["state"] = "FAIL";
     $response["msg"] = "Can only accessed by local administrator!";
     die(json_encode($response));
}

if($_FILES) {
    include 'UploadFile.class.php';
    $dist = 'upload';
    $upload = new UploadFile($dist, 'upfile');
    $data = $upload->upload();
}
if(!empty($upload)){
     if(!empty($data)){
         $response['state'] = "SUCCESS";
         $response["msg"] = $dist."/".$data['filename'];
     }else{
        $response['state'] = "FAIL";
        $response["msg"] = $upload->error;
     }
}

echo json_encode($response);
