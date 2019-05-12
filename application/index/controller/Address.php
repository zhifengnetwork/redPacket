<?php
namespace app\index\controller;
use think\Db;

// 通讯录
class Address extends Base
{

    protected $user;
   	public function _initialize()
    {   
        if (!is_user_login()) {
            //未登陆跳转到登陆
            $this->redirect('/index/index');
            exit;
        }
        $this->user = session('user');
    }

    /**
     * [通讯录页面]
     * @return json
     */
    public function addressList()
    {

        // 获取所有好友数据
        // $list = Db::field('p.id,p.title')
        //         ->name('chat_friends')
        //         ->alias('f')
        //         ->union('SELECT id,title FROM edu_product where deleted=1')
        //         ->where('uid',$this->user['id'])
        //         // ->page('1,10')
        //         ->select();

        // $friends_list = Db::name('chat_');
        return $this->fetch('addressList');
    }

    /**
     * [我的上级页面]
     * @return array
     */
    public function superior(){

        return $this->fetch('superior');
    }

    /**
     * [人工客服页面]
     * @return array
     */
    public function service(){
        
        return $this->fetch('service');
    }

    /**
     * [充值教程页面]
     * @return array
     */
    public function course(){
        
        return $this->fetch('course');
    }


}
