<?php

/**
 * 会员管理
 */
namespace app\admin\controller;

use think\Db;
use think\Session;
use think\Request;
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
    //充值待审核列表
    public function ck_recharge_list(){

        $where = "r.status = 3 ";
        $name = input('get.name');
        $phone = input('get.mobile');
        if( $name != '' ){
            $where .= "and u.nickname = '{$name}' ";    
        }
        if( $phone != '' ){
            $where .= "and u.mobile = {$phone} ";    
        }
        // var_dump($where);exit;

        $list = Db::table('recharge')->alias('r')
                ->join('users u', 'u.id = r.uid', 'LEFT')
                ->where($where)
                ->field('u.nickname,u.mobile,r.id,r.uid,r.amount,r.type,r.proof,r.ordersn,r.time,r.status')->order('r.time desc')->paginate(10);               
         // 获取分页显示
        $page = $list->render();
        // 模板变量赋值
        $this->assign('list', $list);
        $this->assign('page', $page);           
        return $this->fetch('ck_recharge');
    } 

    //充值审核页面
    public function recharge(){
        $id = trim(input('get.id'));
        $info = Db::query("select u.mobile,r.amount,r.uid,r.id from users as u left join recharge as r on u.id = r.uid where r.id = $id");
        $this->assign('info', $info[0]);
        return $this->fetch();
    }

    //充值审核
    public function sub_recharge(){

        $msg = '';
        $flag = 0;
        $id = trim(input('post.id'));
        $desc = trim(input('post.desc'));
        $operate_name = session('admin_user_auth.username');

        if(Request::instance()->isPost()){
            $info = Db::table('recharge')->where('id',$id)->find();
           // var_dump($info);exit;
           $before_money =  Db::name('users')->where('id',$info['uid'])->value('account');
            // 启动事务
            Db::startTrans();
            try{
               
                //更改充值表中交易状态
                Db::table('recharge')->where('id',$id)->update(['status' => 1,'money' => $info['amount']]);

                //账户金额变更记录表中添加记录
                $time = time();
                $data = ['admin' => $operate_name ,'addtime' => $time,'user_id' => $info['uid'],'account' => $before_money,'money' => $info['amount'],'newaccount' => $before_money + $info['amount'],'action' => 0,'desc' => $desc];
                db('account_log')->insert($data);

                //更改用户表中余额
                Db::table('users')->where('id',$info['uid'])->setInc('account',$info['amount']);
                    // 提交事务
                Db::commit();
                $flag = 1;
                $msg = '操作成功';

            } catch (\Exception $e) {

                // 回滚事务
                Db::rollback();
                $msg = '操作失败！';

            }

        }else{

             $msg = '非法请求';

        }

        $string= json_encode(array ('msg'=>$msg,'flag'=>$flag));
        echo $string;

    }




    //收款码管理
    public function receipt_code(){

        $query = "select id,name,img_url,code from receipt_code";
        $list = Db::query($query);
        $this->assign('list', $list);
        return $this->fetch();

    }
    //更换收款码
    public function change_code(){
        $file_wx = request()->file('wx');
        $file_zfb = request()->file('zfb');
        $path = ROOT_PATH . 'public' . DS . 'uploads';

        if($file_wx){
            $info_wx = $file_wx->move($path);
            $wx_path =  $info_wx->getSaveName();
     
            Db::table('receipt_code')->where('code', 'wx')->update(['img_url' => $wx_path]);

        }
        if($file_zfb){
            $info_zfb = $file_zfb->move($path);
            $zfb_path =  $info_zfb->getSaveName();
     
            Db::table('receipt_code')->where('code', 'zfb')->update(['img_url' => $zfb_path]);

        }
        $this->success('更新成功', 'users/receipt_code');



    
    }

    //提现审核列表
    public function tx_list(){
        $where = "r.status = 3 ";
        $name = input('get.name');
        $phone = input('get.mobile');
        if( $name != '' ){
            $where .= "and u.nickname = '{$name}' ";    
        }
        if( $phone != '' ){
            $where .= "and u.mobile = {$phone} ";    
        }
        // var_dump($where);exit;

        $list = Db::table('tixian')->alias('r')
                ->join('users u', 'u.id = r.uid', 'LEFT')
                ->where($where)
                ->field('u.nickname,u.mobile,r.id,r.uid,r.amount,r.type,r.ordersn,r.time,r.status')->order('r.time desc')->paginate(10);
        // var_dump($list);exit;           
         // 获取分页显示
        $page = $list->render();
        // 模板变量赋值
        $this->assign('list', $list);
        $this->assign('page', $page);  

        return $this->fetch();


    }

    //查询提现账号
    public function ajax_account(){
        $msg = '';
        $flag = 0;
        $data = '';
        $paramet = '';
        $id = input('post.id');
        $type = input('post.type');
        if(Request::instance()->isPost()){
           // $info = Db::table('tixian')->where('id',$id)->find();
           // var_dump($info);exit;
            if($id=='' or $type ==''){
                $msg = '参数错误';
            }else{
                $sql ='';
                $method ='';
                if($type==1){
                   $sql = "select uid,name,account from alipay where uid = $id";
                   $method = '支付宝';    
                }else{

                    $sql = "select uid,name,account, bank_name,bank_address from card where uid = $id";
                    $method = '银行卡'; 
                }
                
                $info = Db::query($sql);
                if($info){
                    $data = $info[0];
                    $flag = 1;
                    $msg = 'ok';
                    
                    if($type==1){
                        $paramet = "<div align='center' class='layui-bg-gray layui-text'>提现方式：".$method."<hr>账号：".$data['account']."<hr>真实姓名：".$data['name']."</div>";
                    }
                    if($type==2){
                        $paramet = "<div align='center' class='layui-bg-gray layui-text'>
                        提现方式：".$method."<hr>
                        开户行：".$data['bank_name']."<hr>
                        账号：".$data['account']."<hr>
                        开户地址：".$data['bank_address']."<hr>
                        真实姓名：".$data['name']."</div>";
                    }


                }else{

                    $msg = '账号信息不存在';
                }
                
                
            }


        }else{

            $msg = '非法请求';
        }

        
        $string= json_encode(array ('msg'=>$msg,'flag'=>$flag,'data'=>$paramet));
        echo $string;


    }



    //提现审核
    public function tixian(){

        $msg = '';
        $flag = 0;
        $id = input('post.id');
        $operate_name = session('admin_user_auth.username');

        if(Request::instance()->isPost()){
           $info = Db::table('tixian')->where('id',$id)->find();
           // var_dump($info);exit;
           $before_money =  Db::name('users')->where('id',$info['uid'])->value('account');
            // echo DB::table('users')->getlastsql();exit;
            if($before_money < $info['amount']){
                $msg = '提现金额大于余额';

            }else{
                // 启动事务
                Db::startTrans();
                try{
                   
                    //更改提现记录表中交易状态
                    Db::table('tixian')->where('id',$id)->update(['status' => 1]);

                    //账户金额变更记录表中添加记录
                    $time = time();
                    $data = ['admin' => $operate_name ,'addtime' => $time,'user_id' => $info['uid'],'account' => $before_money,'money' => $info['amount'],'newaccount' => $before_money -$info['amount'],'action' => 1,'desc' => '后台提现审核通过'];
                    db('account_log')->insert($data);

                    //更改用户表中余额
                    Db::table('users')->where('id',$info['uid'])->setDec('account',$info['amount']);
                        // 提交事务
                    Db::commit();
                    $flag = 1;
                    $msg = '操作成功';
    
                } catch (\Exception $e) {

                    // 回滚事务
                    Db::rollback();
                    $msg = '操作失败！';

                }

            } 


        }else{

            $msg = '非法请求';
        }


        $string= json_encode(array ('msg'=>$msg,'flag'=>$flag));
        echo $string;

    }


    //提现审核不通过
    public function cancel_tx(){

        $msg = '';
        $flag = 0;
        $id = input('post.id');
        if(Request::instance()->isPost()){
            //更改提现记录表中交易状态
            $res = Db::table('tixian')->where('id',$id)->update(['status' => 2]);
            if($res){

                $flag = 1;
                $msg = '操作成功';
            }else{

                $msg = '操作失败！';

            }

        }else{

            $msg = '非法请求';

        }   

        $string= json_encode(array ('msg'=>$msg,'flag'=>$flag));
        echo $string;
    }

     



}