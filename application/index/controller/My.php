<?php
namespace app\index\controller;
use think\Db;
use think\Session;
// 我的相关
class My extends Base
{

    private $key;
    public function _initialize()
    {   
        if (!is_user_login()) {
            //未登陆跳转到登陆
            $this->redirect('/index/index');
            exit;
        }
        $this->key = session('user.key');
    }

    /**
     * [设置页面]
     * @return json
     */
    public function mySet()
    {  
        return $this->fetch('my/set_up');
    }

    /**
     * [账号安全页面]
     * @return [type] [description]
     */
    public function accountAndSecurity()
    {
        $user_arr = Db::name('users')->field('id,nickname,mobile,head_imgurl')->where('id',session('user.id'))->find();
        $this->assign('user_arr', $user_arr);
        return $this->fetch('accountAndSecurity');
    }

    /**
     * [通用页面]
     * @return [type] [description]
     */
    public function commonUse()
    {
        return $this->fetch('commonUse');
    }

    /**
     * [我的钱包]
     * @return array
     */
    public function myWallet(){
        
        // 获取余额
        $user_arr = Db::name('users')->field('id,account')->where('id',session('user.id'))->find();
        $this->assign('user_arr', $user_arr);
        return $this->fetch('my_wallet');
    }

    /**
     * [我的账单]
     * @return array
     */
    public function myBill(){

        // 获取余额
        $user_arr = Db::name('users')->field('id,account')->where('id',session('user.id'))->find();
        $this->assign('user_arr', $user_arr);
        return $this->fetch('bill');
    }

    /**
     * [获取用户余额]
     * @return json
     */
    public function getAccount(){
        if(!isPost()){
            return message(0, '非法提交');
        }
        $uid = input('uid/d');
        if(!$uid){
            return message(0, 'no uid');
        }
        $res = Db::name('users')->field('id,account')->where('id', $uid)->find();
        if(!$res){
            return message(0, 'no user');
        }
        return message(1, 'ok',['account'=>$res['account']]);
    }

    /**
     * [我的二维码页面]
     * @return [type] [description]
     */
    public function myQrCode()
    {
        $userid = session('user.id');
        $res = Db::query("select invite_code from users where id = $userid");
        $url = 'http://'.$_SERVER['HTTP_HOST'].'/index/register/register?invite_code='.$res[0]['invite_code'];
        $img =  scerweima($url,$userid);
        $this->assign('img', $img);
        
        return $this->fetch('myQrCode');
    }

    /**
     * [我的团队收益页面]
     * @return [type] [description]
     */
    public function myTeamIncome()
    {
        return $this->fetch('myTeamIncome');
    }

    /**
     * [我的团队页面]
     * @return [type] [description]
     */
    public function myTeam()
    {
        return $this->fetch('myTeam');
    }



}
