<?php

/**
 * 平台公告管理
 */
namespace app\admin\controller;

use think\Db;
use think\Session;
use think\Request;
class Notice extends Common
{
    # 公告列表
    public function index(){

        $list = Db::name('chat_system_notice')->order('create_time desc')->paginate(15);
        $this->assign('list', $list);

        //websocket_url
        $this->assign('websocket_url','ws://'.serverIp().':8282');

        return $this->fetch();
    }

     # 添加 | 公告
    public function add_notice(){

        if($_POST){
            $notice = isset($_POST['content']) ? trim($_POST['content']) : '';
            if(!$notice){
                echo "<script>parent.error('请输入公告内容！')</script>";
                exit;
            }
            $data['content'] = $notice;
            $data['create_time'] = time();
            $res = Db::name('chat_system_notice')->insert($data);
            if($res){
               return json(['status'=>1,'msg'=>'发布成功']);
            }else{
                return json(['status'=>0,'msg'=>'操作失败']);
            }
        }
        return $this->fetch('users/add_notice');
    }

    // 删除公告
    public function del_notice(){
        if($_POST){
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            if(!$id){
                return json(['status'=>1,'msg'=>'缺少参数']);
            }
            $res = Db::name('chat_system_notice')->delete($id);
            if(!$res){
                return json(['status'=>0,'msg'=>'操作失败']);
            }
            return json(['status'=>1,'msg'=>'发布成功']);
        }
        return $this->fetch('users/notice');
    }

}