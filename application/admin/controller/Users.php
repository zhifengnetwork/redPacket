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


        $where['type'] = ['=', 0];
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        if(isset($search['nickname']) && $search['nickname']){
            $nickname = $search['nickname'];
            $where['nickname'] = ['like', "%$nickname%"];
        }
        if(isset($search['mobile']) && $search['mobile']){
            $mobile = $search['mobile'];
            $where['mobile'] = ['like', "%$mobile%"];
        }

        $list = Db::name('users')->where($where)->order('addtime desc')->paginate(15);


        $this->assign('is_lock', [0=>'正常',1=>'已拉黑']);
        $this->assign('list', $list);
        $this->assign('search', $search);
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
        $this->assign('list', $list);
        return $this->fetch('group');
    }


    # 客服列表
    public function servicelist(){

        $where['type'] = ['=', 400];

        $search = isset($_GET['search']) ? $_GET['search'] : '';
        if(isset($search['nickname']) && $search['nickname']){
            $nickname = $search['nickname'];
            $where['nickname'] = ['like', "%$nickname%"];
        }
        if(isset($search['mobile']) && $search['mobile']){
            $mobile = $search['mobile'];
            $where['mobile'] = ['like', "%$mobile%"];
        }

        $list = Db::name('users')->where($where)->order('addtime desc')->paginate(15);

        $this->assign('search', $search);
        $this->assign('is_lock', [0=>'正常',1=>'已拉黑']);
        $this->assign('list', $list);
        return $this->fetch();

    }

    # 添加 | 编辑客服
    public function edit_service(){

        if($_POST){
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $nickname = isset($_POST['nickname']) ? trim($_POST['nickname']) : '';
            $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
            $password = isset($_POST['password']) ? trim($_POST['password']) : '';
            $is_lock = isset($_POST['is_lock']) ? intval($_POST['is_lock']) : 0;
            if(!$nickname){
                echo "<script>parent.error('请输入客服名称！')</script>";
                exit;
            }

            if( !isMobile($mobile) ){
                echo "<script>parent.error('请输入正确的手机号！')</script>";
                exit;
            }

            if( !$id && isMobileRegister($mobile) ){
                echo "<script>parent.error('该手机号已被使用！')</script>";
                exit;
            }

            if( !$id && !$password ){
                echo "<script>parent.error('请输入6~12位登陆密码！')</script>";
                exit;
            }

            $data['nickname'] = $nickname;
            $data['mobile'] = $mobile;
            $data['is_lock'] = $is_lock;

            if($password){
                $len = strlen($password);
                if($len < 6 || $len > 12){
                    echo "<script>parent.error('请输入6~12位登陆密码！')</script>";
                    exit;
                }
                $salt = create_salt();
                $password = minishop_md5($password, $salt);

                $data['salt'] = $salt;
                $data['password'] = $password;
            }

            if(!$id){
                $data['type'] = 400;
                $data['addtime'] = time();
            }

            if($id){
                $res = Db::name('users')->where('id',$id)->update($data);
            }else{
                $res = Db::name('users')->insert($data);
            }

            if($res){
                echo "<script>parent.success()</script>";
            }else{
                echo "<script>parent.error('操作失败，请重试！')</script>";
            }
            exit;
        }

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $info = Db::name('users')->find($id);
        $this->assign('info',$info);
        return $this->fetch();
    }


}