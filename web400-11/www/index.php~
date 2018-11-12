<?php

error_reporting(0);
include('secret.php');

$sandbox_dir = 'sandbox/'.sha1($_SERVER['REMOTE_ADDR']);
global $sandbox_dir;

function myserialize($a, $secret) {
    $b = str_replace("../","./", serialize($a)); 
    return $b.hash_hmac('sha256', $b, $secret); 
}

function myunserialize($a, $secret) { 
    if(substr($a, -64) === hash_hmac('sha256', substr($a, 0, -64), $secret)){
        return unserialize(substr($a, 0, -64));
    }
}
   
class UploadFile {

    function upload($fakename, $content) {
        global $sandbox_dir;
        $info = pathinfo($fakename);
        $ext = isset($info['extension']) ? ".".$info['extension'] : '.txt';
        file_put_contents($sandbox_dir.'/'.sha1($content).$ext, $content);
        $this->fakename = $fakename;        
        $this->realname = sha1($content).$ext;
    }
    function open($fakename, $realname) {
        global $sandbox_dir;
        $analysis = "$fakename is in folder $sandbox_dir/$realname.";
        return $analysis;
    }
}

if(!is_dir($sandbox_dir)) {
    mkdir($sandbox_dir);
}

if(!is_file($sandbox_dir.'/.htaccess')) {
    file_put_contents($sandbox_dir.'/.htaccess', "php_flag engine off");
}

if(!isset($_GET['action'])) {
    $_GET['action'] = 'home';
}


if(!isset($_COOKIE['files'])) {
    setcookie('files', myserialize([], $secret));
    $_COOKIE['files'] = myserialize([], $secret);
}


switch($_GET['action']){
    case 'home':
    default:
        $content = "<form method='post' action='index.php?action=upload' enctype='multipart/form-data'><input type='file' name='file'><input type='submit'/></form>";

        $files = myunserialize($_COOKIE['files'], $secret);
        if($files) {
            $content .= "<ul>";
            $i = 0;
            foreach($files as $file) {
                $content .= "<li><form method='POST' action='index.php?action=changename&i=".$i."'><input type='text' name='newname' value='".htmlspecialchars($file->fakename)."'><input type='submit' value='Click to edit name'></form><a href='index.php?action=open&i=".$i."' target='_blank'>Click to show locations</a></li>";
                $i++;
            }
            $content .= "</ul>";
        }
        echo $content;
        break;
    case 'upload':
        if($_SERVER['REQUEST_METHOD'] === "POST") {
            if(isset($_FILES['file'])) {
                $uploadfile = new UploadFile;
                $uploadfile->upload($_FILES['file']['name'], file_get_contents($_FILES['file']['tmp_name']));
                $files = myunserialize($_COOKIE['files'], $secret);
                $files[] = $uploadfile;
                setcookie('files', myserialize($files, $secret));
                header("Location: index.php?action=home");
                exit;
            }
        }
        break;
    case 'changename':
        if($_SERVER['REQUEST_METHOD'] === "POST") {        
            $files = myunserialize($_COOKIE['files'], $secret);
            if(isset($files[$_GET['i']]) && isset($_POST['newname'])){
                $files[$_GET['i']]->fakename = $_POST['newname'];
            }
            setcookie('files', myserialize($files, $secret));            
        }
        header("Location: index.php?action=home");
        exit;
    case 'open':
        $files = myunserialize($_COOKIE['files'], $secret);
        if(isset($files[$_GET['i']])){
            echo $files[$_GET['i']]->open($files[$_GET['i']]->fakename, $files[$_GET['i']]->realname);
        }
        exit;
    case 'reset':
        setcookie('files', myserialize([], $secret));
        $_COOKIE['files'] = myserialize([], $secret);
        array_map('unlink', glob("$sandbox_dir/*"));
        header("Location: index.php?action=home");
        exit;
}
