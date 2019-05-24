<?php
namespace app\index\controller;
use think\Session;
use think\Request;
use think\Db;
class Index extends Base
{

	public function _initialize()
    {   
        if (is_user_login()) {
            //登陆跳转到消息页
            $this->redirect('index/message/messageList');
            exit;
        }
    }

    // 登录页面
    public function index()
    {    
        return $this->fetch('login');
    }

    // 登录密码找回
    public function password(){
        // echo 1;exit;
        return $this->fetch();


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

    //找回密码
    public function find_password(){
        $flag = 0;
        $msg = '';

        if (Request::instance()->isPost()){
            $mobile = input('post.mobile');
            $code = trim(input('post.code'));
            $pw1 = trim(input('post.pw1'));
            $pw2 = trim(input('post.pw2'));
            $res = Db::query("select mobile from users where mobile = $mobile");
            if( $pw1!=$pw2 ){
                $msg = '两次密码输入不一致';

            }elseif(!isMobile($mobile)){
                $msg = '请填写正确手机号';  

            }elseif($code==''){
                $msg = '请输入短信验证码'; 

            }elseif(!$res){
                $msg = '用户不存在';   
            }else{


                if($code!=session('smscode')){
                    $msg = '短信验证码错误';

                }else{

                    // 密码处理
                    $salt = create_salt();
                    $password = minishop_md5($pw1,$salt);
                    //更新数据
                    $res = Db::table('users')->where('mobile',$mobile)->update(['password' => $password,'salt' => $salt]);
                    if($res){

                        $flag = 1;
                        $msg = '成功';   
                    }else{
                        $msg = '失败';
                    }
                }
                
            }   
        }else{

            $msg = '非法请求';


        }
        $string= json_encode(array ('msg'=>$msg,'flag'=>$flag));
        echo $string;

    }




}
