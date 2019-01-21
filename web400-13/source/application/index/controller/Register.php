<?php
/**
 * Created by PhpStorm.
 * User: wh1t3P1g
 * Date: 2018/8/19
 * Time: 14:41
 */

namespace app\index\controller;
use think\Session;
use think\Url;
use think\Db;


class Register extends Base{

    public function __construct()
    {
        parent::__construct();
        $privilege = Session::get("privilege");
        $privilege = $privilege ? $privilege : getSessionUser();
        if($privilege){
            $this->success("Already Login!!!",Url::build('/'),'',0);
        }
    }

    public function index()
    {
        if($this->request->isGet()){
            $this->assign('baseUrl',$this->baseUrl);
            return $this->fetch();
        }else{
            $username = $this->request->post('username','','htmlspecialchars');
            $password = $this->request->post('password','','md5');
            $repassword = $this->request->post('repassword','','md5');
            if($password === $repassword){
                Db::name('users')
                    ->insert([
                        'username' => $username,
                        'password' => $password,
                    ]);
                $this->success("Register Success!!!",Url::build('/index/login'),'',1);
            }else{
                $this->error("Register Error, different Password!!!",Url::build('/index/register'),'',1);
            }
        }
    }
}