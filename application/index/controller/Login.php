<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;

class Login extends Controller
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
            return message(0, '非法提交');
        }

        session('user',null); // 清空上一次session
    	$param = input('post.');
        $param['password'] = trim($param['password']);
        if(!isMobile($param['mobile'])){
            return message(0, '手机号无效');
        }
        // 判断用户是否存在
        $is_user = getUserInfo($param['mobile'], 1); // 类型1
        if(!$is_user){
            return message(0, '用户不存在');
        }
        if(!$param['password']){
            return message(0, '密码不可为空');
        }
        // 判断密码长度6位数字密码
        $pw_len = strlen($param['password']);
        if($pw_len<6 || $pw_len>6){
            return message(0, '请输入6位数字密码');
        }
        // 比对密码
        if($is_user['password'] !== minishop_md5($param['password'], $is_user['salt'])){
            return message(0, '密码错误');
        }
        // 判断用户是否被禁用
        if($is_user['is_lock']==1){
            return message(0, '用户被禁用');
        }
        // 设置session
        unset($is_user['password']);
        unset($is_user['salt']);
        $is_user['uid'] = $is_user['id'];

        // 一个账号一处登录begin
        //生成随机串
        $login_token['login_token'] =md5(time().$is_user['id']);
        //存到数据库 
        $in_token = Db::name('users')->where(['id'=>$is_user['id']])->update($login_token);
        //存到session
        $_SESSION['login_token'] = $login_token['login_token'];
        // end
        // 生成key到session 好友转账时使用验证
        $is_user['key'] = md5(time().mt_rand(0,1000).'redpak');
        session('user',$is_user);
        return message(1, '登录成功');

    }

    // 退出登录
    public function outLogin(){
        session('user',null);
        $this->redirect('/index/index');
        // $this->fetch('login');
    }
    




}
