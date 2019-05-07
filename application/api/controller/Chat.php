<?php
/**
 * Created by --.
 * User: c
 * Date: 2019/5/7
 * Time: 11:29
 */
namespace app\api\controller;

use think\Controller;
use think\Db;
use think\Request;

class Chat extends Controller{

    /**
     *文本消息的数据持久化
     */
    public function save_message(){
        if(Request::instance()->isAjax()){

            $message = input("post.");
            $datas['fromid']=$message['fromid'];
            $datas['from_name']= $this->getName($datas['fromid']);
            $datas['toid']=$message['toid'];
            $datas['to_name']= $this->getName($datas['toid']);
            $datas['content']=$message['data'];
            $datas['time']=$message['time'];
            $datas['is_read']=$message['is_read'];
            $datas['type'] = $message['send_type'];
            $res = Db::name("chat_info")->insert($datas);
            if(!$res){
                return json(['code'=>0, 'msg'=>'failed', 'data'=>'']);
            }
            return json(['code'=>1, 'msg'=>'ok', 'data'=>'']);
        }else{
            return json(['code'=>0, 'msg'=>'非法请求', 'data'=>'']);
        }
    }

    /**
     * 根据用户id返回用户昵称
     */
    public function getName($uid){

        $userinfo = Db::name("users")->where('id',$uid)->field('nickname')->find();
        return $userinfo['nickname'];
    }
    

}