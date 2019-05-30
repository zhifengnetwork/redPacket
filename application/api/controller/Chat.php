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
use think\Config;

//  聊天相关API数据处理
class Chat extends Controller{

    protected $defaultName = 996; // 默认昵称
    /**
     *文本消息的数据持久化
     */
    // public function save_message(){

    //     if(!Request::instance()->isAjax()){
    //         return json(['code'=>0, 'msg'=>'非法请求', 'data'=>'']);
    //     }
    //     $message = input('post.');
    //     // 处理客户端发来的消息 data(文本类型消息)
    //     $text   = nl2br(htmlspecialchars($message['data']));
    //     // $text = preg_replace('/($s*$)|(^s*^)/m', '',$text);  

    //     $fromid = intval($message['fromid']);
    //     $toid   = intval($message['toid']);
    //     $send_type = $message['type']=='say'?1:2;
    //     if($message['type']=='say'){
    //         $send_type = 1;
    //     }else if($message['type']=='say_img'){
    //         $send_type = 2;
    //     }else if($message['type']=='transfer'){
    //         $send_type = 3;
    //     }else{
    //         $send_type = 1; // 否则1文本
    //     }

    //     // 判断当前session是否和发送者的uid一致
    //     if($fromid == session('user.id')){
    //         $datas['fromid'] = $fromid;
    //         $datas['from_name'] = $this->getName($fromid);
    //         $datas['toid'] = $toid;
    //         $datas['to_name'] = $this->getName($toid);
    //         $datas['content'] = $text;
    //         $datas['time'] = time();
    //         // $datas['is_read'] = $message['is_read'];
    //         $datas['type'] = $send_type;
    //         $res = Db::name("chat_info")->insert($datas);
    //         if(!$res){
    //             return json(['code'=>0, 'msg'=>'failed', 'data'=>'']);
    //         }
    //     }
    //     return json(['code'=>1, 'msg'=>'save ok', 'data'=>'']); 
    // }

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

         $message = Db::name('chat_info')->where('(fromid=:fromid and toid=:toid) || (fromid=:toid1 and toid=:fromid1)',['fromid'=>$fromid,'toid'=>$toid,'toid1'=>$toid,'fromid1'=>$fromid])->limit($count-20,20)->order('id')->select();
        }else{
          $message = Db::name('chat_info')->where('(fromid=:fromid and toid=:toid) || (fromid=:toid1 and toid=:fromid1)',['fromid'=>$fromid,'toid'=>$toid,'toid1'=>$toid,'fromid1'=>$fromid])->order('id')->select();
        }

        foreach ($message as $k=>$v) {
            $message[$k]['content'] = htmlspecialchars_decode($v['content']);
        }

        return $message;
    }


     /**
     * 上传表情图片，返回图片地址,并且入库
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

        if($file['size']/1024>9120){
            // return ['status'=>'img is too large'];
            return json(['code'=>0, 'msg'=>'图片过大', 'data'=>""]);
        }
        // 判断当前session是否和发送者的uid一致
        if($fromid == session('user.id')){
            $uploadpath = ROOT_PATH.'public/upload/chat_img';
            $month = date("Y-m-d");
            //如果文件夹不存在则建立; 
            $fileNewPath = $uploadpath.'/'.$month.'/'; 
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
        }else{
            return json(['code'=>0, 'msg'=>'上传失败!', 'data'=>""]);
        }
    }


     /**
     * @param $uid
     * 根据uid来获取它的头像
     */
    public function get_head_one($uid){

        $fromhead = Db::name('users')->where('id',$uid)->field('head_imgurl')->find();

        return $fromhead['head_imgurl'];
    }

    /**
     * @param $fromid
     * @param $toid
     * 根据fromid来获取fromid与toid发送的未读消息。
     */
    public function getCountNoread($fromid,$toid){

        return Db::name('chat_info')->where(['fromid'=>$fromid,'toid'=>$toid,'is_read'=>0])->count('id');
    }

    /**
     * @param $fromid
     * @param $toid
     * 根据fromid和toid来获取他们聊天的最后一条数据
     */
    public function getLastMessage($fromid,$toid){

        $info = Db::name('chat_info')->where('(fromid=:fromid&&toid=:toid)||(fromid=:fromid2&&toid=:toid2)',['fromid'=>$fromid,'toid'=>$toid,'fromid2'=>$toid,'toid2'=>$fromid])->order('id DESC')->limit(1)->find();
        $info['content'] = htmlspecialchars_decode($info['content']);
        return $info;
    }


    /**
     * 根据fromid来获取当前用户聊天列表
     */
    public function get_list(){

        if(!Request::instance()->isAjax()){
            return json(['code'=>0, 'msg'=>'非法请求', 'data'=>'']);
        }
        $fromid = input('id');
        $info  = Db::name('chat_info')->field(['fromid','toid','from_name'])->where('toid',$fromid)->group('fromid')->select();
        $rows = array_map(function($res){
            return [
                'head_url'=>$this->get_head_one($res['fromid']),
                'username'=>$res['from_name'],
                'countNoread'=>$this->getCountNoread($res['fromid'],$res['toid']),
                'last_message'=>$this->getLastMessage($res['fromid'],$res['toid']),
                'chat_page'=>'/index/message/onetoonedialog.html?fromid='.$res['toid'].'&toid='.$res['fromid']
                // 'chat_page'=>"{:url('index/message/oneToOnedialog',['fromid'=>".$res['toid'].','."'toid'=>".$res['fromid']."])}"
            ];

        },$info);
        return $rows;
    }

    /**
     * 根据room_id来获取5分钟内的红包
     */
    public function getRedList(){
        
        if(!Request::instance()->isAjax()){
            return json(['code'=>0, 'msg'=>'非法请求', 'data'=>'']);
        }
        $room_id = input('room_id/d');
        $map['room_id'] = $room_id;
        $info = Db::name('chat_red_master')->alias('d')
                ->field('d.id,d.uid,d.room_id,d.ray_point,d.ray_point_num,d.money,u.nickname,head_imgurl')
                ->join('users u','d.uid = u.id')
                ->where($map)
                ->whereTime('d.create_time','-5 minute')
                ->select();
        foreach($info as $k => $value) {
            if(!$value['ray_point_num']){
                $info[$k]['ray_point'] = '';
                // $info[$k]['money'] = floatval($value['money']);
            }
        }
        return $info;
    }
    
    /**
     * 修改聊天状态
     * 
     */
    public function changeNoRead(){
        if(!Request::instance()->isAjax()){
            return json(['code'=>0, 'msg'=>'非法请求', 'data'=>'']);
        }
        $fromid = input('toid/d');
        $toid = input('fromid/d');
        $res = Db::name('chat_info')->where(['fromid'=>$fromid,"toid"=>$toid])->update(['is_read'=>1]);
        // $a = Db::name('chat_info')->getLastSql();
        return json(['code'=>1, 'msg'=>'update'.$res, 'data'=>'']);
    }

    /**
     * 红包超时退回
     */
    public function sendBackRed()
    {
        // 获取所有大于5分钟的红包
        $send_key = Config::get('SEND_KEY');
        $key = input('key/s');
        if(!$key || $send_key!=$key){
            echo 'no...';exit;
        }
        $map['m.time_out'] = 0;
        $map['m.all_get'] = 0;
        $red_all = Db::name('chat_red_master')->alias('m')
                ->field('m.id,m.uid,m.room_id,m.num,m.money,m.create_time,m.time_out,m.all_get')
                ->where('m.time_out',0)
                ->select();
        // 如果超时退回标记主表time_out=1 以及从表type=2，并且统计红包是否全部领取，如果全部领取标记主表all_get=1
        $out_time = 350; // 过期时间
        $i = 0;
        if($red_all){
            foreach($red_all as $k=>$v){
                // 判断当前时间是否大于等于红包创建时间+过期时间
                if(time() >= $v['create_time']+$out_time){
                    // 根据当前主表id获取从表没被抢的红包记录 统计没被领取红包总金额
                    $no_get_money = Db::name('chat_red_detail')->where(['m_id'=>$v['id'], 'type'=>0])->sum('money');
                    // 退还金额到对应用户
                    if($no_get_money){
                        $out_money_res = Db::name('users')->where(['id'=>$v['uid']])->setInc('account', $no_get_money);
                        if($out_money_res){
                            // 修改状态
                            $out_update_res = Db::name('chat_red_detail')->where(['m_id'=>$v['id'], 'type'=>0])->update(['type'=>2]);
                            $out_update_res2 = Db::name('chat_red_master')->where(['id'=>$v['id']])->update(['time_out'=>1,'all_get'=>1]);
                            // 插入日志
                            $out_money_log = [
                                'from_id' => $v['uid'],
                                'uid' => $v['uid'],
                                'm_id' => $v['id'],
                                'red_money' => $v['money'],
                                'money' => $no_get_money,
                                'type' => 12,
                                'create_time' => time(),
                                'remake' => '红包退回'
                            ];
                            $out_money_log_res = Db::name('chat_red_log')->insert($out_money_log);
                        }else{
                            echo 'out red update red err\n';
                        }
                    }else{
                        // 修改主表标记全部领取
                        $out_update_res3 = Db::name('chat_red_master')->where(['id'=>$v['id']])->update(['all_get'=>1]);
                        continue;
                    }
                }
                $i++;
            }
            echo 'out red '.$i;
        }else{
            echo 'no order\n';
            exit;
        }


    }
}