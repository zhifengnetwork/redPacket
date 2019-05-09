<?php
namespace app\index\controller;

class Index extends Base
{
    // 登录页面
    public function index()
    {
        return $this->fetch('login/login');
    }

    // // 聊天页面私聊与群聊
    // public function chatMessage(){

    //     $param = $this->request->param();
    //     $fromid = intval($param['fromid']); 
    //     $toid = intval($param['toid']);
    //     $room_id = intval($param['room_id']);
    //     $this->assign('fromid',$fromid);
    //     $this->assign('toid',$toid);
    //     $this->assign('room_id',$room_id);
    //     return $this->fetch('message/message');

    // }

}
