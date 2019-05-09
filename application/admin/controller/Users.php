<?php

/**
 * 会员管理
 */
namespace app\admin\controller;

use think\Db;

class Users extends Common
{
    # 会员列表
    public function index(){

        $list = Db::name('users')->order('addtime desc')->paginate(15);


        $this->assign('is_lock', [0=>'正常',1=>'已拉黑']);
        $this->assign('list', $list);
        return $this->fetch();
    }

    # 修改会员状态
    public function edit_status(){
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $is_lock = isset($_POST['is_lock']) ? intval($_POST['is_lock']) : 0;
        if($id){
            
            $res = Db::name('users')->where('id',$id)->update(['is_lock' => $is_lock]);
            if($res){
                return json(['status'=>1]);
            }
        }
        return json(['status'=>0]);
    }

    /**
     * +-----------------------------------------
     * | 群组列表
     * +-----------------------------------------
     */
    public function groupList(){

        $list = Db::name('chat_group')->order('id desc')->paginate(15);
        // $this->assign('is_lock', [0=>'正常',1=>'已拉黑']);
        $this->assign('list', $list);
        return $this->fetch('group');
    }


}