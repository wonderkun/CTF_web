<?php
/**
 * Created by PhpStorm.
 * User: wh1t3P1g
 * Date: 2018/8/17
 * Time: 15:50
 */

namespace app\index\controller;

use think\Controller;
use think\Url;
use think\Request;

class Base extends Controller
{

    public function __construct()
    {
        parent::__construct();
        if(!file_exists('public/install.lock')){
            $this->error("Not Installed!!!",
                Url::build('/install'),'',1);
        }
        $this->baseUrl = Url::build('/');
        $this->request = Request::instance();
    }

}