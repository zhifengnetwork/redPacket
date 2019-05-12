<?php
namespace app\index\controller;

class Find extends Base
{

    // 发现页面
    public function index()
    {    
        return $this->fetch('find');
    }

}
