<?php
namespace app\index\controller;
use think\Db;
// 我的相关
class Recharge extends Base
{

    private $key;
    public function _initialize()
    {   
        if (!is_user_login()) {
            //未登陆跳转到登陆
            $this->redirect('/index/index');
            exit;
        }
        $this->key = session('user.key');
    }

    /**
     * [用户值]
     * @return array
     */
    public function index(){
        
        
        return $this->fetch('recharge');
    }


    public function subReCharge(){


        var_dump($_POST);exit;
    }


}
