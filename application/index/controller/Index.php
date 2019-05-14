<?php
namespace app\index\controller;

class Index extends Base
{

	public function _initialize()
    {   
        if (is_user_login()) {
            //登陆跳转到消息页
            $this->redirect('index/message/messageList');
            exit;
        }
    }

    // 登录页面
    public function index()
    {    
        return $this->fetch('login');
    }

}
