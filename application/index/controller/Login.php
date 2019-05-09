<?php
namespace app\index\controller;
use think\Db;
use think\Session;

class Login extends Base
{

    /**
     * [用户登录操作]
     * @param string $mobile 手机号
     * @param string $password 密码
     * @return string
     */
    public function userToLogin()
    {
    	if(!isPost()){
            return json(['code'=>0, 'msg'=>'非法提交', 'data'=>""]);
        }
    	$param = input('post.');
        if(!isMobile($param['mobile'])){
            return json(['code'=>0, 'msg'=>'手机号无效', 'data'=>""]);
        }
        if(!$param['password']){
            return json(['code'=>0, 'msg'=>'密码不可为空', 'data'=>""]);
        }
        // 判断用户是否存在
        $is_user = getUserInfo($param['mobile'], 1); // 类型1
        if(!$is_user){
            return json(['code'=>0, 'msg'=>'用户不存在!', 'data'=>""]);
        }
        // 比对密码
        if($is_user['password'] !== minishop_md5($param['password'], $is_user['salt'])){
            return json(['code'=>0, 'msg'=>'密码错误!', 'data'=>""]);
        }
        // 判断用户是否被禁用
        if($is_user['is_lock']==1){
            return json(['code'=>0, 'msg'=>'用户被禁用!', 'data'=>""]);
        }
        // 设置session
        unset($is_user['password']);
        unset($is_user['salt']);
        session('user',$is_user);
        return json(['code'=>1, 'msg'=>'登录成功', 'data'=>""]);
    }

    // 退出登录
    public function outLogin(){
        session('user',null);
        $this->fetch('login');
    }
    


}
