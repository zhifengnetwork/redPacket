<?php
namespace app\index\controller;
use think\Request;
use think\Db;
// 我的相关
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


    public function subReCharge(){
        $flag=0;
        $msg='';
        if (Request::instance()->isPost()) {
            $amout = input('post.money');
            $base64_img = input('post.img');
            // print_r($base64_img);exit;
            // $img =  base_img($base64_img,'cz');
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
/*        $base64_img = base64_decode(tri$_POST['img']));

        $file_path = "./upload";
        $filename = date("Ymd",time()).time();

        if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_img, $result)){

             $type= $result[2];

             $img_path = $file_path."/".$filename.".".$type;
             echo $img_path;exit;
             if(file_put_contents($img_path, base64_decode(str_replace($result[1], '', $base64_img)))){

                   echo  "/upload/".$filename.".".$type;

             }

        }*/


        


    }


}
