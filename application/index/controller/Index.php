<?php
namespace app\index\controller;

class Index extends Base
{

    // 登录页面
    public function index()
    {    
        return $this->fetch('login');
    }

}
