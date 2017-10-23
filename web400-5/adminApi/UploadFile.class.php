<?php
//decode by QQ:270656184 http://www.yunlu99.com/

class UploadFile {
    public $error = '';

    protected $field;
    protected $allow_ext;
    protected $allow_size;
    protected $dist_path;
    protected $new_path;

    function __construct($dist_path, $field='upfile', $new_name='random', $allow_ext=['gif', 'jpg', 'jpeg', 'png'], $allow_size=102400)
    {
        $this->field = $field;
        $this->allow_ext = $allow_ext;
        $this->allow_size = $allow_size;
        $this->dist_path = realpath($dist_path);

        if ($new_name === 'random') {
            $this->new_name = uniqid();
        } elseif (is_string($new_name)) {
            $this->new_name = $new_name;
        } else {
            $this->new_name = null;
        }
    }

    protected function codeToMessage($code) 
    { 
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE: 
                $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini"; 
                break; 
            case UPLOAD_ERR_FORM_SIZE: 
                $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                break; 
            case UPLOAD_ERR_PARTIAL: 
                $message = "The uploaded file was only partially uploaded"; 
                break; 
            case UPLOAD_ERR_NO_FILE: 
                $message = "No file was uploaded"; 
                break; 
            case UPLOAD_ERR_NO_TMP_DIR: 
                $message = "Missing a temporary folder"; 
                break; 
            case UPLOAD_ERR_CANT_WRITE: 
                $message = "Failed to write file to disk"; 
                break; 
            case UPLOAD_ERR_EXTENSION: 
                $message = "File upload stopped by extension"; 
                break; 
            default: 
                $message = "Unknown upload error"; 
                break; 
        } 
        return $message; 
    } 

    protected function error($info)
    {
        $this->error = $info;
        return false;
    }

    public function upload()
    {
        if(empty($_FILES[$this->field])) {
            return $this->error('上传文件为空');
        }
        if(is_array($_FILES[$this->field]['error'])) {
            return $this->error('一次只能上传一个文件');
        }
        if($_FILES[$this->field]['error'] != UPLOAD_ERR_OK) {
            return $this->error($this->codeToMessage($_FILES[$this->field]['error']));
        }
        $filename = !empty($_POST[$this->field]) ? $_POST[$this->field] : $_FILES[$this->field]['name'];
        if(!is_array($filename)) {
            $filename = explode('.', $filename);
        }
        foreach ($filename as $name) {
            if(preg_match('#[<>:"/\\|?*.]#is', $name)) {
                return $this->error('文件名中包含非法字符');
            }
        }

        if($_FILES[$this->field]['size'] > $this->allow_size) {
            return $this->error('你上传的文件太大');
        }
        if(!in_array($filename[count($filename)-1], $this->allow_ext)) {
            return $this->error('只允许上传图片文件');
        }

        // 用.分割文件名，只保留首尾两个字符串，防御Apache解析漏洞
        $origin_name = current($filename);
        $ext = end($filename);
        $new_name = ($this->new_name ? $this->new_name : $origin_name) . '.' . $ext;
        $target_fullpath = $this->dist_path . DIRECTORY_SEPARATOR . $new_name;

        // 创建目录
        if(!is_dir($this->dist_path)) {
            mkdir($this->dist_path);
        }

        if(is_uploaded_file($_FILES[$this->field]['tmp_name']) && move_uploaded_file($_FILES[$this->field]['tmp_name'], $target_fullpath)) {
            // Success upload
        } elseif (rename($_FILES[$this->field]['tmp_name'], $target_fullpath)) {
            // Success upload
        } else {
            return $this->error('写入文件失败，可能是目标目录不可写');
        }
        
        return [
            'name' => $origin_name,
            'filename' => $new_name,
            'type' => $ext
        ];
    }
}