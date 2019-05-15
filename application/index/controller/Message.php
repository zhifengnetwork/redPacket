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


}
