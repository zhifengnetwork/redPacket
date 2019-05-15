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

    // protected function _initialize(){
    //     if(!Request::instance()->isAjax()){
    //         return json(['code'=>0, 'msg'=>'非法请求', 'data'=>'']);
    //         exit;
    //     }
    //     
    // }

    protected $defaultName = 996; // 默认昵称
    /**
     *文本消息的数据持久化
     */
    public function save_message(){

        if(!Request::instance()->isAjax()){
            return json(['code'=>0, 'msg'=>'非法请求', 'data'=>'']);
        }

        $message = input("post.");
        $datas['fromid'] = intval($message['fromid']);
        $datas['from_name'] = $this->getName($datas['fromid']);
        $datas['toid'] = intval($message['toid']);
        $datas['to_name'] = $this->getName($datas['toid']);
        $datas['content'] = $message['data'];
        $datas['time'] = $message['time'];
        $datas['is_read'] = $message['is_read'];
        $datas['type'] = $message['send_type'];
        $res = Db::name("chat_info")->insert($datas);
        if(!$res){
            return json(['code'=>0, 'msg'=>'failed', 'data'=>'']);
        }
        return json(['code'=>1, 'msg'=>'save ok', 'data'=>'']);
        
    }

    /**
     * 入库时根据用户id返回用户昵称
     */
    protected function getName($uid){

        $userinfo = Db::name("users")->where('id',$uid)->field('nickname')->find();
        return $userinfo['nickname'];
    }

    /**
     * 根据用户id获取聊天双方的头像信息
     */
    public function get_head(){

        if(!Request::instance()->isAjax()){
            return json(['code'=>0, 'msg'=>'非法请求', 'data'=>'']);
        }
        $fromid = input('fromid/d');
        $toid = input('toid/d');
        $frominfo = Db::name('users')->where('id',$fromid)->field('head_imgurl')->find();
        $toinfo = Db::name('users')->where('id',$toid)->field('head_imgurl')->find();

        $data = [
            'from_head' => $frominfo['head_imgurl'],
            'to_head'   => $toinfo['head_imgurl']
        ];
        return json(['code'=>1, 'msg'=>'ok', 'data'=>$data]);
    }

    /**
     * 根据用户id返回用户昵称
     */
    public function get_name(){

        if(!Request::instance()->isAjax()){
            return json(['code'=>0, 'msg'=>'非法请求', 'data'=>'']);
        }
        $uid = input('uid/d');
        $fromid = input('fromid/d');
        $toinfo = Db::name('users')->where('id',$uid)->field('nickname')->find();
        $frominfo = Db::name('users')->where('id',$fromid)->field('nickname')->find();
        $data = [
            'toname' => $toinfo['nickname'],
            'fromname' => $frominfo['nickname']?$frominfo['nickname']:$this->defaultName
        ];
        return json(['code'=>1, 'msg'=>'ok', 'data'=>$data]);
    }

    /**
     * 页面加载返回聊天记录
     */
    public function load(){

        if(!Request::instance()->isAjax()){
            return json(['code'=>0, 'msg'=>'非法请求', 'data'=>'']);
        }
        $fromid = input('fromid/d');
        $toid   = input('toid/d');

        $count = Db::name('chat_info')->where('(fromid=:fromid and toid=:toid) || (fromid=:toid1 and toid=:fromid1)',
            ['fromid'=>$fromid,'toid'=>$toid,'toid1'=>$toid,'fromid1'=>$fromid])->count('id');

        if($count>=10){

         $message = Db::name('chat_info')->where('(fromid=:fromid and toid=:toid) || (fromid=:toid1 and toid=:fromid1)',['fromid'=>$fromid,'toid'=>$toid,'toid1'=>$toid,'fromid1'=>$fromid])->limit($count-10,10)->order('id')->select();
        }else{
          $message = Db::name('chat_info')->where('(fromid=:fromid and toid=:toid) || (fromid=:toid1 and toid=:fromid1)',['fromid'=>$fromid,'toid'=>$toid,'toid1'=>$toid,'fromid1'=>$fromid])->order('id')->select();
        }

        return $message;
    }


     /**
     * 上传表情图片，返回图片地址
     */
    public function uploadimg(){

        $file = $_FILES['file'];
        $fromid = input('fromid/d');
        $toid = input('toid/d');
        $online = input('online');

        $suffix =  strtolower(strrchr($file['name'],'.'));
        $type = ['.jpg','.jpeg','.gif','.png'];
        if(!in_array($suffix,$type)){
            // return ['status'=>'img type error'];
            return json(['code'=>0, 'msg'=>'图片类型错误', 'data'=>""]);
        }

        if($file['size']/1024>5120){
            // return ['status'=>'img is too large'];
            return json(['code'=>0, 'msg'=>'图片过大', 'data'=>""]);
        }

        $uploadpath = ROOT_PATH.'public\\upload';
        $month = date("Y-m-d");
        //如果文件夹不存在则建立; 
        $fileNewPath = $uploadpath.'\\'.$month.'\\'; 
        if(!file_exists($fileNewPath)){ 
            mkdir($fileNewPath); 
        } 

        $filename =  uniqid("chat_img_",false);
        $file_up = $fileNewPath.$filename.$suffix;
        $re = move_uploaded_file($file['tmp_name'],$file_up);

        if($re){
            $name = $month.'/'.$filename.$suffix;
            $data['content'] = $name;
            $data['fromid'] = $fromid;
            $data['toid'] = $toid;
            $data['from_name'] = $this->getName($data['fromid']);
            $data['to_name'] = $this->getName($data['toid']);
            $data['time'] = time();
            $data['is_read'] = $online;
            $data['type'] = 2; // 图片类型
            $message_id = Db::name('chat_info')->insertGetId($data);
            if($message_id){
                // return['status'=>'ok','img_name'=>$name];
                return json(['code'=>1, 'msg'=>'上传成功!', 'data'=>['img_name'=>$name]]);
            }else{
                // return ['status'=>'false'];
                return json(['code'=>0, 'msg'=>'上传入库失败!', 'data'=>""]);
            }
        }else{
            return json(['code'=>0, 'msg'=>'上传失败!', 'data'=>""]);
        }
    }
    

}