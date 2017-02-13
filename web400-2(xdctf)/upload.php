<?php
/**
 * Created by PhpStorm.
 * User: phithon
 * Date: 15/10/14
 * Time: 下午8:45
 */

require_once "common.inc.php";

if($_FILES)
{
    $file = $_FILES["upfile"];
    if($file["error"] == UPLOAD_ERR_OK) {
        $name = basename($file["name"]);
        $path_parts = pathinfo($name);

        if(!in_array($path_parts["extension"], array("gif", "jpg", "png", "zip", "txt"))) {
            exit("error extension");
        }
        $path_parts["extension"] = "." . $path_parts["extension"];

        $name = $path_parts["filename"] . $path_parts["extension"];
        
        $path_parts['filename'] = addslashes($path_parts['filename']);

        $sql = "select * from `file` where `filename`='{$path_parts['filename']}' and `extension`='{$path_parts['extension']}'";
        $fetch = $db->query($sql);
        if($fetch->num_rows>0) {
            exit("file is exists");
        }

        if(move_uploaded_file($file["tmp_name"], UPLOAD_DIR . $name)) {

            $sql = "insert into `file` ( `filename`, `view`, `extension`) values( '{$path_parts['filename']}', 0, '{$path_parts['extension']}')";
            $re = $db->query($sql);
            if(!$re) {
                print_r($db->error);
                exit;
            }
            $url = "/" . UPLOAD_DIR . $name;
            echo "Your file is upload, url:
                <a href=\"{$url}\" target='_blank'>{$url}</a><br/>
                <a href=\"/\">go back</a>";
        } else {
            exit("upload error");
        }

    } else {
        print_r(error_get_last());
        exit;
    }
}