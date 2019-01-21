<?php
/**
 * Created by PhpStorm.
 * User: wh1t3P1g
 * Date: 2018/8/19
 * Time: 14:39
 */

namespace app\index\controller;
use think\Session;
use think\Url;
use think\Db;


class Note extends Base{
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
        if($this->request->isGet()){
            $this->assign('baseUrl',$this->baseUrl);
            return $this->fetch();
        }
        else{
            $content = $this->request->post('content','','htmlspecialchars');
            Db::name('notes')
                ->insert([
                    'userid'=>Session::get('privilege'),
                    'content' => $content,
                ]);
            $this->success('Add Note Success!!!',Url::build('/index/note/getall'),'',1);
        }
    }

    public function getall()
    {
        $notes = Db::name('notes')
            ->field('content')
            ->where('userid','=',Session::get('privilege'))
            ->select();
        $this->assign('notes',$notes);
        $this->assign('baseUrl',$this->baseUrl);
        return $this->fetch();
    }
}