<?php
namespace app\index\controller;
use think\Db;
// 消息
class Message extends Base
{
    public function _initialize()
    {   
        if (!is_user_login()) {
            //未登陆跳转到登陆
            $this->redirect('/index/index');
            exit;
        }
    }

    /**
     * [消息页面]
     * @return json
     */
    public function messageList()
    {
        // 获取平台群列表
        $group_list = Db::name('chat_group')->select();

        $this->assign('group_list', $group_list);
        $this->assign('user', session('user'));
        $this->assign('fromid', session('user.id'));
        return $this->fetch('messageList');
    }

    /**
     * [一对一聊天页面]
     * @return toid [聊天对象uid]
     */
    public function oneToOnedialog(){

        $toid = input('toid/d');
        $this->assign('toid', $toid);
        $this->assign('fromid', session('user.id'));
        return $this->fetch('dialog');
    }


    /**
     * [点击我的头像进入设置页面]
     * @return json
     */
    public function mySet()
    {
        return $this->fetch('my/set_up');
    }


}
