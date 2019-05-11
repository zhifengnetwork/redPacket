<?php

/**
 * 会员管理
 */
namespace app\admin\controller;

use think\Db;
use think\Session;

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

        $list = Db::name('users')->where($where)->order('addtime desc,id desc')->paginate(15);
        $last_name = [0 => '--'];
        if($list){
            foreach($list as $v){
                $ids[$v['pid']] = $v['pid'];
            }
            if(isset($ids) && count($ids) > 1){
                $ids = implode(',',$ids);
                $last_arr = Db::name('users')->where(['id'=>['in',$ids]])->field('id,nickname,mobile')->select();
                if($last_arr){
                    foreach($last_arr as $v){
                        $last_name[$v['id']] = $v['nickname'] ?  $v['nickname'].' '.$v['mobile'] : $v['mobile'];
                    }
                }
            }
        }

        $this->assign('is_lock', [0=>'正常',1=>'已拉黑']);
        $this->assign('list', $list);
        $this->assign('search', $search);
        $this->assign('last_name', $last_name);
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

    # 账户金额变动
    public function edit_account(){

        if($_POST){
            $search = isset($_POST['search']) ? intval($_POST['search']) : 0;
            $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
            $type = isset($_POST['type']) ? intval($_POST['type']) : 0;
            $money = isset($_POST['money']) ? intval($_POST['money']*100)/100 : 0;
            $desc = isset($_POST['desc']) ? addslashes($_POST['desc']) : '';

            $info = Db::name('users')->where('mobile', $mobile)->field('id,nickname,account,type')->find();
            if(!$info){
                return json(['status'=>0,'msg'=>'会员不存在']);
            }

            if($search){
                $info['status'] = 1;
                $info['typename'] = $info['type'] == 0 ? '普通会员' : '客服';
                return json($info);
            }

            if($money == 0){
                return json(['status'=>0,'msg'=>'金额不能为0']);
            }
            if(!$desc){
                return json(['status'=>0,'msg'=>'请输入备注']);
            }

            $admin = Session::get('admin_user_auth.username');
            $account = $info['account'];

            if($type == 0){
                $account = $account + $money;
            }else{
                $account = $account - $money > 0 ? $account - $money : 0;
            }

            $res = Db::name('users')->where('id', $info['id'])->update(['account' => $account]);
            if($res){
                Db::name('account_log')->insert([
                        'admin' => $admin,
                        'addtime' => time(),
                        'user_id' => $info['id'],
                        'account' => $info['account'],
                        'money' => $money,
                        'newaccount' => $account,
                        'action' => $type,
                        'desc' => $desc,
                    ]);
                
                
                return json(['status' => 1, 'msg' => '操作成功！正在跳转...']);
                
            }

            return json(['status' => 0, 'msg' => '操作失败！请重试']);
        }

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
                echo "<script>parent.error('请输入6位数字登陆密码！')</script>";
                exit;
            }

            $data['nickname'] = $nickname;
            $data['mobile'] = $mobile;
            $data['is_lock'] = $is_lock;

            if($password){
                if(!preg_match("/^[\d]{6}$/",$password)){
                    echo "<script>parent.error('请输入6位数字登陆密码！')</script>";
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

    # 余额记录
    public function account_log(){

        $where['a.id'] = ['>', 0];
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        if(isset($search['nickname']) && $search['nickname']){
            $nickname = $search['nickname'];
            $where['b.nickname'] = ['like', "%$nickname%"];
        }
        if(isset($search['mobile']) && $search['mobile']){
            $mobile = $search['mobile'];
            $where['b.mobile'] = ['like', "%$mobile%"];
        }
        if(isset($search['action']) && intval($search['action']) > 0){
            $action = intval($search['action']) - 1;
            $where['a.action'] = ['=', $action];
        }

        $list = Db::name('account_log')->where($where)->alias('a')->join('users b','a.user_id = b.id','left')->field('a.*,b.nickname,b.mobile')->order('a.addtime desc')->paginate(10);
        

        $this->assign('action', [0=>'充值',1=>'扣除']);
        $this->assign('list', $list);
        $this->assign('search', $search);
        return $this->fetch();
    }
}