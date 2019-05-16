<?php
namespace app\index\controller;
use think\Db;
// 我的相关
class My extends Base
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
     * [设置页面]
     * @return json
     */
    public function mySet()
    {  
        return $this->fetch('my/set_up');
    }

   


}
