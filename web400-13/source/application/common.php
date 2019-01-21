<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
define('CS',md5(base64_encode($_SERVER['HTTP_HOST'])));

function init(){
    $args[] = 'mysql';
    $args[] = 'mysql';
    $args[] = 'root';
    $args[] = '465b236607c695f3';
    $args[] = '3306';
    if(check_database_connection($args)){
        file_put_contents('public/init.lock','1');
    }
}

function generate_database_file($request){

    $tpl = file_get_contents("application/install/config.tpl");
    $tpl = str_replace("[type]",$request->post('dbtype','','htmlspecialchars'),$tpl);
    $tpl = str_replace("[hostname]",$request->post('dbhost','','htmlspecialchars'),$tpl);
    $tpl = str_replace("[username]",$request->post('dbuser','','htmlspecialchars'),$tpl);
    $tpl = str_replace("[password]",$request->post('dbpass','','htmlspecialchars'),$tpl);
    $tpl = str_replace("[hostport]",$request->post('dbport','','htmlspecialchars'),$tpl);
    file_put_contents("application/database.php",$tpl);
    file_put_contents("public/install.lock","1");

}

function check_database_connection($args){

    try{
        $db = new mysqli($args[1],$args[2],$args[3]);
        $db->query("CREATE DATABASE IF NOT EXISTS `thinkphp`;");
        $db = \think\Db::connect([
            // 数据库类型
            'type'        => $args[0],
            // 数据库连接DSN配置
            'dsn'         => '',
            // 服务器地址
            'hostname'    => $args[1],
            // 数据库名
            'database'    => 'thinkphp',
            // 数据库用户名
            'username'    => $args[2],
            // 数据库密码
            'password'    => $args[3],
            // 数据库连接端口
            'hostport'    => $args[4],
            // 数据库连接参数
            'params'      => [],
            // 数据库编码默认采用utf8
            'charset'     => 'utf8',
            // 数据库表前缀
            'prefix'      => 'think_',
        ]);
        $db->execute("DROP TABLE IF EXISTS `think_notes`;");
        $db->execute("CREATE TABLE `think_notes` (
  `userid` int(11) DEFAULT NULL,
  `content` varchar(255) DEFAULT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;");
        $db->execute("DROP TABLE IF EXISTS `think_users`;");
        $db->execute("CREATE TABLE `think_users` (
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;");
        return true;
    }catch(\Exception $e){
        return false;
    }
}


function getSessionUser()
{
    $info = decode(\think\Cookie::get('info'));
    if(is_array($info) && isset($info['username']) && isset($info['password'])){
        $user = \think\Db::name('users')
            ->where('username','=',$info['username'])
            ->where('password','=',$info['password'])
            ->find();
        \think\Session::set('privilege',$user['id']);
        \think\Session::set('username',$user['username']);
        \think\Cookie::set('info',encode($user));
        return $user['id'];
    }
    unset($info);
    return null;
}

function decode($info)
{
    $key = CS;
    $info = urldecode($info);
    $kl = strlen($key);
    $il = strlen($info);
    for($i = 0; $i < $il; $i++)
    {
        $p = $i%$kl;
        $info[$i] = chr(ord($info[$i])-ord($key[$p]));
    }
    $info = unserialize($info);
    return $info;
}

function encode($info)
{
    $info = serialize($info);
    $key = CS;
    $kl = strlen($key);
    $il = strlen($info);
    for($i = 0; $i < $il; $i++)
    {
        $p = $i%$kl;
        $info[$i] = chr(ord($info[$i])+ord($key[$p]));
    }
    return urlencode($info);
}

if(!file_exists('public/init.lock')){
    init();
}
