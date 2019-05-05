<?php
namespace app\index\controller;

class Index extends Base
{
    public function index()
    {
        $fromUid = 110;  // 当前用户uid
        $toUid   = 201;    // 接收信息方uid  
        $roomId  = 301;    // 群id
        $this->assign('fromUid',$fromUid);
        $this->assign('toUid',$toUid);
        $this->assign('roomId',$roomId);
        return $this->fetch();

    }

    // 聊天页面私聊与群聊
    public function chatMessage(){

        $param = $this->request->param();
        $from_uid = intval($param['from_uid']); 
        $to_uid = intval($param['to_uid']);
        $room_id = intval($param['room_id']);
        $this->assign('from_uid',$from_uid);
        $this->assign('to_uid',$to_uid);
        $this->assign('room_id',$room_id);
        return $this->fetch('message/message');

    }

}
