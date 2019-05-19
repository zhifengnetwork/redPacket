<?php
namespace app\index\controller;
use think\Db;
// 消息与我的相关
class Message extends Base
{
    private $key;
    public function _initialize()
    {   
        if (!is_user_login()) {
            //未登陆跳转到登陆
            $this->redirect('/index/index');
            exit;
        }
        $this->key = session('user.key');
    }

    /**
     * [消息页面]
     * @return json
     */
    public function messageList()
    {
        // 获取平台群列表
        $group_list = Db::name('chat_group')->select();

        $this->assign('group_list', $group_list);
        $this->assign('user', session('user'));
        $this->assign('fromid', session('user.id'));
        return $this->fetch('messageList');
    }

    /**
     * [一对一聊天页面]
     * @return toid [聊天对象uid]
     */
    public function oneToOnedialog(){

        $user_arr = Db::name('users')->field('id,account')->where('id',session('user.id'))->find();
        $toid = input('toid/d');
        $this->assign('toid', $toid);
        $this->assign('fromid', session('user.id'));
        $this->assign('user_arr', $user_arr);
        $this->assign('key', $this->key);
        return $this->fetch('dialog');
    }


    /**
     * [点击我的头像进入设置页面]
     * @return json
     */
    public function mySet()
    {
        return $this->fetch('my/set_up');
    }

    /**
     * [好友转账]
     * @param int $from_uid [转出方uid]
     * @param int $to_uid [收款方uid]
     * @param decimal $money [转账金额]
     * @return json
     */
    public function friendTransfer(){
        
        if(!isPost()){
            return message(0, '非法提交');
        }
        $param = input('post.');
        var_dump($param);die;

    }


    /**
     * [好友转账密码验证及数据入库]
     * @return [type] [description]
     */
    public function checkPwd(){

        if(!isPost()){
            return message(0, '非法提交');
        }
        $password = input('pwd/d');
        $money = input('money');
        $key = input('key/s');
        $to_uid = input('to_uid/d');

        $user = Db::name('users')->field('id,account,password,salt')->where('id',session('user.id'))->find();
        if(!$user){
            return message(0, '用户不存在');
        }
        // 判断接收用户是否存在
        $to_user = Db::name('users')->field('id')->where('id',$to_uid)->find();
        if(!$to_user){
            return message(0, '对方用户不存在');
        }
        if($key != $this->key){
            return message(0,'错误参数-key');
        }
        if(!is_numeric($money)){
            return message(0,'金额有误');
        }
        if($money>$user['account']){
            return message(0,'转账金额不足');
        }
        if(!$password){
            return message(0,'请输入密码');
        }
        $pw_len = strlen($password);
        // 判断密码长度6位数字密码
        if($pw_len<6 || $pw_len>6){
            return message(0, '请输入6位数字密码');
        }
        if(!is_numeric($password)){
            return message(0, '密码格式有误');
        }
        // 比对密码
        if($user['password'] !== minishop_md5($password, $user['salt'])){
            return message(0, '密码错误');
        }
        // 转账数据入库
        $time = time();
        $order_no = createOrderNo();
        $from_data = [
            'from_uid' => session('user.id'),
            // 'get_uid' => $to_uid,
            'order_no' => $order_no,
            'flag' => '-',
            'money' => $money,
            'create_time' => $time,
            'type' => 1 // 转出
        ];
        $to_data = [
            // 'from_uid' => session('user.id'),
            'get_uid' => $to_uid,
            'order_no' => $order_no,
            'flag' => '+',
            'money' => $money,
            'create_time' => $time,
            'type' => 2 // 收款
        ];
        
        // 启动事务
        Db::startTrans();
        try{

            // 扣减转账方账户
            $res1 = Db::name('users')->where('id', $user['id'])->setDec('account', $money);
            // 增加收方账户
            $res2 = Db::name('users')->where('id', $to_uid)->setInc('account', $money);
            $res3 = Db::name('user_transfer')->insert($from_data);
            $res4 = Db::name('user_transfer')->insert($to_data);
            $user_account = Db::name('users')->field('id,account')->where('id',session('user.id'))->find();
            // 提交事务
            Db::commit();
            return message(1, '转账成功',['account'=>$user_account['account']]);
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return message(0, '网络异常,稍后再试');
        }

    }


}
