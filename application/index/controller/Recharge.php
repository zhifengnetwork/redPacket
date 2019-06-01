<?php
namespace app\index\controller;
use think\Request;
use think\Db;
use think\Session;
// 充值提现相关
class Recharge extends Base
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
     * [用户值]
     * @return array
     */
    public function index(){


        $res = Db::query('select id,name,img_url,code from receipt_code');
        $this->assign('info', $res);
        return $this->fetch('recharge');
    }

    //充值申请提交
    public function subReCharge(){
        $flag=0;
        $msg='';
        if (Request::instance()->isPost()) {
            $amout = input('post.money');
            $base64_img = input('post.img');
            $type = input('post.type');
            $img =  uploadImg($base64_img);
            if($img =='上传失败'){
                $msg = '提交失败！';    
            }else{
                $orderNumber = createOrderNo();
                $time = time();
                $id = session('user.id');
                $data = ['uid' => $id, 
                        'amount' => $amout,
                        'type' =>$type,
                        'proof' =>$img,
                        'ordersn' =>$orderNumber,
                        'time' => $time,
                        'status' => 3];
                $res = Db::table('recharge')->insert($data);
                if($res>0){
                    $flag = 1;
                    $msg = '提交成功！';
                }else{
                    $msg = '提交失败！';
                }     

            }    
                

        }else{

            $msg = '非法请求';


        }
        $string= json_encode(array ('msg'=>$msg,'flag'=>$flag));
        echo $string;


    }
    //提现    
    public function tixian(){
        $userid =  session('user.id');
        $info = Db::table('users')->where('id',$userid)->field('account')->find();

        $this->assign('info', $info);    
        return $this->fetch('withdrawal');
    }

    //提现申请提交
    public function sub_tixian(){
        $flag = 0;
        $msg = '';
        $userid = session('user.id');
        $money = input('post.money');
        $method = input('post.method');
        // $method = 3;    
        $info = Db::table('users')->where('id',$userid)->field('account')->find();    
        $time = time();   
        $orderNumber = createOrderNo();

        if($method==1){
           $check = Db::table('alipay')->where('uid',$userid)->find();           
        }elseif($method==2){
            $check = Db::table('card')->where('uid',$userid)->find();
        }else{
           $check = false;     

        }
      
        if(!$check){

            $msg = '请先完善支付宝和银行卡信息';
            $flag = 3;    
        }elseif($money<100){
           $msg='提现金额不能小于100';     

        }elseif($money > $info['account']){

            $msg='余额不足';    


        }elseif($method!='1' and $method!='2'){
             $msg='提现方式不支持';    


        }elseif(!Request::instance()->isPost()) {

             $msg='非法请求';   



        }else{



            $data = ['uid' => $userid, 'amount' => $money ,'type' => $method,'status' => 3,'time' => $time,'ordersn' => $orderNumber];
            $res = db('tixian')->insert($data);
            if( $res ){
                $flag = 1;
                $msg = '提现申请成功！等待审核';
            }else{

                $flag = 0;
                $msg = '提现申请失败！';
            } 

        }    
        $string= json_encode(array ('msg'=>$msg,'flag'=>$flag));
        echo $string;    


    }



    //提现方式
    public function withdrawal_way(){
        $userid =  session('user.id');
        $zfb = Db::table('alipay')->where('uid',$userid)->field('id,name,uid,account')->find();
        $card = Db::table('card')->where('uid',$userid)->field('id,name,uid,account,bank_name,bank_address')->find();
        $this->assign('zfb', $zfb);
        $this->assign('card', $card);
        
        
        return $this->fetch();

    }
    //编辑支付宝
    public function pay(){


        $userid =  session('user.id');
        $info = Db::table('alipay')->where('uid',$userid)->find();
        $this->assign('info', $info);
        return $this->fetch();

    }


    // 支付宝编辑提交
    public function edit_pay(){
        // var_dump($_POST);exit;
        $msg = '';
        $flag = 0;
        $userid =  session('user.id');
        $account = input('post.account');
        $name = input('post.name');
        if(Request::instance()->isPost()){
           $info = Db::table('alipay')->where('uid',$userid)->find();
           if( $info ){ //存在 编辑
                $res = Db::table('alipay')->where('uid',$userid)->update(['name' => $name,'account' => $account]);

                if( $res ){
                    $flag = 1;
                    $msg = '更新成功！';
                }else{


                    $msg = '更新失败！';
                }
           }else{   //不存在 添加
                $data = ['uid' => $userid, 'account' => $account ,'name' => $name];
                $res = db('alipay')->insert($data);
                if( $res ){
                    $flag = 1;
                    $msg = '添加成功！';
                }else{


                    $msg = '添加失败！';
                }
           }
        }else{

            $msg = '非法请求';
        }


        $string= json_encode(array ('msg'=>$msg,'flag'=>$flag));
        echo $string;

    }


    //编辑银行卡
    public function editcard(){
        $userid =  session('user.id');
        $info = Db::table('card')->where('uid',$userid)->find();
        $this->assign('info', $info);
        return $this->fetch();
    }
    //银行卡编辑提交
    public function sub_card(){
        $msg = '';
        $flag = 0;
        $userid =  session('user.id');
        $account = input('post.account');
        $name = input('post.name');
        $bank = input('post.bank');
        $address = input('post.address');

        if(Request::instance()->isPost()){
           $info = Db::table('card')->where('uid',$userid)->find();
           if( $info ){ //存在 编辑
                $res = Db::table('card')->where('uid',$userid)->update(['name' => $name,'account' => $account,'bank_name' => $bank,'bank_address' => $address]);

                if( $res ){
                    $flag = 1;
                    $msg = '更新成功！';
                }else{

                    $flag = 0;
                    $msg = '更新失败！';
                }
           }else{   //不存在 添加
                $data = ['uid' => $userid, 'account' => $account ,'name' => $name,'bank_name' => $bank,'bank_address' => $address];
                $res = db('card')->insert($data);
                if( $res ){
                    $flag = 1;
                    $msg = '添加成功！';
                }else{

                    $flag = 0;
                    $msg = '添加失败！';
                }
           }
        }else{

            $msg = '非法请求';
        }


        $string= json_encode(array ('msg'=>$msg,'flag'=>$flag));
        echo $string;


    }

    //支付密码
    public function paypwd(){
        $userid =  session('user.id');
        $info = Db::table('users')->where('id',$userid)->field('pay_pwd,mobile')->find();
        $flag = 0;
        if($info['pay_pwd']==null){
            $flag = 1; //未设置支付密码

        }

        $mobile = $info['mobile'];
        $this->assign('flag', $flag);
        $this->assign('mobile', $mobile);
        return $this->fetch('payment');

    }


    //设置支付密码
    public function set_paypwd(){

        $flag = 0;
        $msg = '';
        $userid =  session('user.id');
        if (Request::instance()->isPost()){
            $pw1 = trim(input('post.pw1'));
            $pw2 = trim(input('post.pw2'));
            if($pw1 != $pw2 ){
                $msg = '两次密码输入不一致';
            }else{

                //更新数据
                $salt = Db::table('users')->where('id',$userid)->field('salt')->find();

                $pay_pwd = minishop_md5($pw1,$salt['salt']);
                $res = Db::table('users')->where('id',$userid)->update(['pay_pwd' => $pay_pwd]);
                if($res){

                    $flag = 1;
                    $msg = '支付密码设置成功';   
                }else{
                    $msg = '支付密码设置失败';
                }

            }

        }else{

            $msg = '非法请求';


        }
        $string= json_encode(array ('msg'=>$msg,'flag'=>$flag));
        echo $string;

    }

    //重置支付密码、
    public function reset_paypwd(){

        $flag = 0;
        $msg = '';
        $userid =  session('user.id');
        if (Request::instance()->isPost()){
            $oldpwd = trim(input('post.oldpwd'));
            $newpwd = trim(input('post.newpwd'));
            if($newpwd =='' ){
                $msg = '请输入新密码';
            }elseif($oldpwd ==''){

                $msg = '请输入旧密码';

            }else{

                //更新数据
                $info = Db::table('users')->where('id',$userid)->field('salt,pay_pwd')->find();
                $old_pay_pwd = minishop_md5($oldpwd,$info['salt']);

                $new_pay_pwd =  minishop_md5($newpwd,$info['salt']);

                if($old_pay_pwd != $info['pay_pwd']){
                    $msg ='原密码输入错误';

                }else{
                    $res = Db::table('users')->where('id',$userid)->update(['pay_pwd' => $new_pay_pwd]);
                    if($res){

                        $flag = 1;
                        $msg = '支付密码重置成功';   
                    }else{
                        $msg = '支付密码重置失败';
                    }

                }

                

            }

        }else{

            $msg = '非法请求';


        }
        $string= json_encode(array ('msg'=>$msg,'flag'=>$flag));
        echo $string;



    }

    //找回支付密码
    public function find_paypwd(){
        $flag = 0;
        $msg = '';
        $userid =  session('user.id');
        if (Request::instance()->isPost()){
            $pass1 = trim(input('post.pass1'));
            $pass2 = trim(input('post.pass2'));
            $code = trim(input('post.code'));
            if($pass1 =='' ){
                $msg = '请输入新密码';
            }elseif($pass2 ==''){

                $msg = '请输入确认密码';

            }elseif($pass2!=$pass1){
                $msg = '两次密码输入不一致';    

            
            }elseif($code==''){
               $msg = '请输入验证码';     

            }elseif($code!=session('smscode')){

                $msg = '短信验证码错误';


            }else{



                //更新数据
                $info = Db::table('users')->where('id',$userid)->field('salt,pay_pwd')->find();
                
                $new_pay_pwd = minishop_md5($pass1,$info['salt']);
                $res = Db::table('users')->where('id',$userid)->update(['pay_pwd' => $new_pay_pwd]);
                if($res){

                    $flag = 1;
                    $msg = '支付密码找回成功';   
                }else{
                    $msg = '支付密码找回失败';
                }

    

            }

        }else{

            $msg = '非法请求';


        }
        $string= json_encode(array ('msg'=>$msg,'flag'=>$flag));
        echo $string;
   

    }    



        // 获取短信验证码
    public function smscode(){
       
        $flag = 0;
        $msg = '';
        if (Request::instance()->isPost()){
            $mobile = input('post.mobile');
            // $code = rand('100000','999999');
            $res = Db::query("select mobile from users where mobile = $mobile");
            if(!$res){
                $msg = '用户不存在';   
            }else{
                $code = '888888';//短信接口调用
                Session::set('smscode',$code);
                $flag = 1;
                $msg = '发送成功';   

            }   
        }else{

            $msg = '非法请求';


        }
        $string= json_encode(array ('msg'=>$msg,'flag'=>$flag));
        echo $string;


    }


}
