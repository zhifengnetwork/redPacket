<?php
namespace app\index\controller;
use think\Db;
// 通讯录
class AddressList extends Base
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
     * [通讯录页面]
     * @return json
     */
    public function addressList()
    {

        return $this->fetch('addressList');
    }


}
