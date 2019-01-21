<?php
/**
 * Created by PhpStorm.
 * User: wh1t3P1g
 * Date: 2018/8/19
 * Time: 14:40
 */

namespace app\index\controller;
use think\Db;
use think\Session;
use think\Url;
use think\Cookie;

class Login extends Base{

    public function __construct()
    {
        parent::__construct();
        $privilege = Session::get("privilege");
        $privilege = $privilege ? $privilege : getSessionUser();
        if($privilege){
            $this->success("Already Login!!!",Url::build('/'),'',0);
        }
    }

    public function index(){
        if($this->request->isGet()){
            $this->assign('baseUrl',$this->baseUrl);
            return $this->fetch();
        }else{
            $username = $this->request->post('username','','htmlspecialchars');
            $password = $this->request->post('password','','md5');
            $result = Db::name('users')
                        ->where('username','=',$username)
                        ->where('password','=',$password)
                        ->find();
            if($result){
                Session::set('privilege',$result['id']);
                Session::set('username',$result['username']);
                Cookie::set('info',encode($result));
                $this->success("Login success!!!",$this->baseUrl,'',0);
            }else{
                $this->error(
                    'Login error, username or password error!!!',
                    Url::build('/index/login'),'',1);
            }
        }

    }
}