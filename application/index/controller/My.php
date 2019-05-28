<?php
namespace app\index\controller;
use think\Db;
use think\Session;
use think\Request;
// 我的相关
class My extends Base
{

    private $key;
    public function _initialize()
    {   
        parent::_initialize();
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
        $userid = session('user.id');
        // 获取余额
        $user_arr = Db::name('users')->field('id,account')->where('id',session('user.id'))->find();
        //充值记录
        // $rechargeList =  Db::query("select id,uid,time,amount,status from recharge where uid = $userid");
        $rechargeList = Db::name('recharge')->where(['uid'=>$userid])->order('time desc')->select();
        //提现记录
        // $txList =  Db::query("select id,uid,time,amount,status from tixian where uid = $userid");
        $txList = Db::name('tixian')->where(['uid'=>$userid])->order('time desc')->select();
        // $redbagList = Db::query("select id,get_uid,money,get_time,status from chat_red_detail where get_uid = $userid and type =1");
        $redbagList = Db::name('chat_red_log')->where(['uid'=>$userid])->order('create_time desc')->select();
        // var_dump($rechargeList);exit;
        $this->assign('rechargeList', $rechargeList);
        $this->assign('txList', $txList);
        $this->assign('redbagList', $redbagList);
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
        $userid = session('user.id');
        $nums =  Db::query("select count(id) as nums from users where pid = $userid");

        $this->assign('nums', $nums[0]['nums']);

        return $this->fetch('myTeamIncome');
    }

    /**
     * [我的团队页面]
     * @return [type] [description]
     */
    public function myTeam()
    {
        
        $userid = session('user.id');
        $list =  Db::query("select id,pid,head_imgurl,nickname from users where pid = $userid");
        $this->assign('list', $list);
        return $this->fetch('myTeam');
    }

    public function activityCenter(){
        return $this->fetch('activity_center');
    }

    /*
    用户个人信息
    */
    public function personInfo(){

        $info = Db::table('users')->where('id',session('user.id'))->field('id,nickname,head_imgurl,mobile')->find();
        // var_dump($info);exit;
         $this->assign('info', $info);
        return $this->fetch('personal_center');

    }

    public function editname(){

        return $this->fetch('edit_name');

    }
    // 个人用户名称
    public function sub_name(){
        $flag=0;
        $msg='';
        $userid = session('user.id');
        if (Request::instance()->isPost()) {
            $nickname = trim(input('post.nickname'));
            if($nickname==''){
                $msg='昵称不能为空！';
            }else{
                $res = Db::table('users')->where('id',$userid)->update(['nickname' => $nickname]);
                if( $res ){
                    $flag = 1;
                    $msg = '成功！';
                }else{

                    $flag = 0;
                    $msg = '失败！';
                }


            }

        }else{
            $msg='非法请求';


        }   

        $string= json_encode(array ('msg'=>$msg,'flag'=>$flag));
        echo $string;
    }
    //个人用户图像修改
    public function sub_photo(){

        $flag=0;
        $msg='';
        $userid = session('user.id');
        if (Request::instance()->isPost()) {
   
            $base64_img = input('post.photo');
        
            if($base64_img==''){
                $msg='图像不能为空！';
            }else{
                $head_imgurl =  uploadImg($base64_img); 
                if($head_imgurl =='上传失败'){
                        $msg = '失败！';  
                }else{            

                    $res = Db::table('users')->where('id',$userid)->update(['head_imgurl' => $head_imgurl]);
                    if( $res ){
                        $flag = 1;
                        $msg = '成功！';
                    }else{

                        $msg = '失败！';
                    }
                }

            }

        }else{
            $msg='非法请求';


        }   

        $string= json_encode(array ('msg'=>$msg,'flag'=>$flag));
        echo $string;   

    }



}
