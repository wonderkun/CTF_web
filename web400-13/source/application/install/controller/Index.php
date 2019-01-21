<?php
/**
 * Created by PhpStorm.
 * User: wh1t3P1g
 * Date: 2018/8/17
 * Time: 16:26
 */

namespace app\install\controller;

use think\Controller;
use think\Url;
use think\Request;

class Index extends Controller{

    public function __construct()
    {
        parent::__construct();
        if(file_exists('public/install.lock')){
            $this->success("Already Installed!!!",Url::build('/'));
        }
        $this->request = Request::instance();
    }

    public function index(){
        if($this->request->isGet()){
            $this->assign('baseUrl',Url::build('/'));
            return $this->fetch('index');
        }else{
            $args[] = $this->request->post('dbtype','','htmlspecialchars');
            $args[] = $this->request->post('dbhost','','htmlspecialchars');
            $args[] = $this->request->post('dbuser','','htmlspecialchars');
            $args[] = $this->request->post('dbpass','','htmlspecialchars');
            $args[] = $this->request->post('dbport','','htmlspecialchars');
            if(check_database_connection($args)){
                generate_database_file($this->request);
                $this->success(
                    "Information setup success!!!",
                    \think\Url::build("/index/login"),'',1);
            }else{
                $this->error("Database Settings Error!!!");
            }
        }
    }
}