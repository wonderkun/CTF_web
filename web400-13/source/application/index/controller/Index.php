<?php
namespace app\index\controller;

use think\Session;
use think\Url;
use think\Cookie;
class Index extends Base
{
    public function __construct()
    {
        parent::__construct();
        $privilege = Session::get("privilege");
        $privilege = $privilege ? $privilege : getSessionUser();
        if(!$privilege){
            $this->error("Not Login!!!",Url::build('/index/login'));
        }
    }

    public function index()
    {
        $this->assign('username',Session::get('username'));
        $this->assign('baseUrl',$this->baseUrl);
        return $this->fetch();
    }

    public function logout(){
        Session::delete('privilege');
        Cookie::delete('info');
        $this->success('Logout success!!!',Url::build('/index/login'),'',1);
    }
}
