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


}
