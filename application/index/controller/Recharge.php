<?php
namespace app\index\controller;
use think\Request;
use think\Db;
// 充值提现相关
class Recharge extends Base
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
     * [用户值]
     * @return array
     */
    public function index(){

        
        return $this->fetch('recharge');
    }

    //充值申请提交
    public function subReCharge(){
        $flag=0;
        $msg='';
        if (Request::instance()->isPost()) {
            $amout = input('post.money');
            $base64_img = input('post.img');
            $img =  uploadImg($base64_img);
            if($img =='上传失败'){
                $msg = '提交失败！';    
            }else{
                $orderNumber = createOrderNo();
                $time = time();
                $id = session('user.id');
                $data = ['uid' => $id, 
                        'amount' => $amout,
                        'type' =>1,
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
        $flag=0;
        $msg='';
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

            $msg = '请先完善支付宝或银行卡信息';    
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
        $msg = '';
        $flag = 0;
        $userid =  session('user.id');
        $account = input('post.account');
        $name = input('post.name');
        if(Request::instance()->isPost()){
           $info = Db::table('alipay')->where('uid',$userid)->find();
           if( $info ){ //存在 编辑
                $res = Db::table('alipay')->where('uid',$userid)->update(['name' => $name],['account' => $account]);
                if( $res ){
                    $flag = 1;
                    $msg = '更新成功！';
                }else{

                    $flag = 0;
                    $msg = '更新失败！';
                }
           }else{   //不存在 添加
                $data = ['uid' => $userid, 'account' => $account ,'name' => $name];
                $res = db('alipay')->insert($data);
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

}
