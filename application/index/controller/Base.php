<?php
namespace app\index\controller;
// use app\common\model\Config;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;

/*
 * 公共控制器
 */
class Base extends Controller
{
    /*
     * 初始化 (保留不要注释)
     */
    public function _initialize()
    {
    	// 一个账号一处登录
	    // 获取数据库中存的登录值
	    $uid = session('user.uid');
	    $m_login = Db::name('users')->field('id,login_token')->where(['id' => $uid])->find();
	    // 和session中的值对比，不一样则退出上一次登录
	    if($_SESSION['login_token'] != $m_login['login_token']){
	        session('user',null);
        	$this->redirect('/index/index');
	    }

    }

}
