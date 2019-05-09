<?php
namespace app\index\controller;
use think\Db;

class Message extends Base
{
    // 登录页面
    public function messageList()
    {
        return $this->fetch('messageList');
    }


}
