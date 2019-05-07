<?php
namespace app\index\controller;

class Index extends Base
{
    public function index()
    {
        $fromid = 1;        // 当前用户uid
        $toid   = 3;        // 接收信息方uid  
        $room_id  = 4;      // 群id
        // 获取当前用户的好友
        // todo
        
        // 获取平台群以及客服列表
        // todo
        
        $this->assign('fromid',$fromid);
        $this->assign('toid',$toid);
        $this->assign('room_id',$room_id);
        return $this->fetch();

    }

    // 聊天页面私聊与群聊
    public function chatMessage(){

        $param = $this->request->param();
        $fromid = intval($param['fromid']); 
        $toid = intval($param['toid']);
        $room_id = intval($param['room_id']);
        $this->assign('fromid',$fromid);
        $this->assign('toid',$toid);
        $this->assign('room_id',$room_id);
        return $this->fetch('message/message');

    }

}
