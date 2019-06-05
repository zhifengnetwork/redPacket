<?php
namespace app\index\controller;
use think\Db;
use think\Config;
use think\Request;
use think\Session;

// 消息与我的相关
class Message extends Base
{
    private $key; // 自动生成用户的验证key
    private $send_key; // 推送信息时带上
    public function _initialize()
    {   
        parent::_initialize();
        if (!is_user_login()) {
            //未登陆跳转到登陆
            $this->redirect('/index/index');
            exit;
        }
        $this->key = session('user.key');
        $this->send_key = Config::get('SEND_KEY');
    }

    /**
     * [消息页面]
     * @return json
     */
    public function messageList()
    {
        // 获取平台群列表
        $group_list = Db::name('chat_group')->where(['status'=>0])->select();
        // var_dump($group_list);exit;
        // 返回绑定群url
        foreach($group_list as $k=>$v){
            // if($v['id']==1){ // 10-30红包群
            //     $group_list[$k]['group_chat_url'] = '/index/message/groupChat.html?room_id=1001';
            //     $group_list[$k]['id'] = 1001;
            // }
            // if($v['id']==2){ // 30-800红包群
            //     $group_list[$k]['group_chat_url'] = '/index/message/groupChat.html?room_id=1002';
            //     $group_list[$k]['id'] = 1002;
            // }
            $group_list[$k]['group_chat_url'] = '/index/message/groupChat.html?room_id='.$v['id'];
            // 系统公告id=4
            if($v['id']==4){
                $group_list[$k]['group_chat_url'] = '/index/message/noticShow.html';
            }
        }

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
        
        if( !is_complete(session('user.id')) ){
            $this->redirect('/index/my/personInfo');    
        };
        $user_arr = Db::name('users')->field('id,account')->where('id',session('user.id'))->find();
        $toid = input('toid/d');
        $this->assign('toid', $toid);
        $this->assign('fromid', session('user.id'));
        $this->assign('user_arr', $user_arr);
        $this->assign('key', $this->key);
        $this->assign('send_key',$this->send_key);
        return $this->fetch('dialog');
    }

    /**
     * 公告显示页面
     */
    public function noticShow(){
        $roomid = 4;
        $room_id = Db::name('chat_group')->field('id,name')->where(['id'=>$roomid])->find();
        $this->assign('room_id', $room_id['id']);
        return $this->fetch('notice');
    }

    /**
     * 获取公告列表
     */
    public function getNoticeList(){

        $list = Db::name('chat_system_notice')->limit(20)->select();
        foreach ($list as $key => $value) {
            $list[$key]['time'] = date('Y-m-d H:i:s', $value['create_time']);
        }
        return $list;
    }

    /**
     * 获取最后一条公告
     */
    public function getLastNotice(){
        $one_info = Db::name('chat_system_notice')->order('create_time desc')->limit(1)->find();
        return $one_info;
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
     * [好友转账密码验证及数据入库]
     * @param int $from_uid [转出方uid]
     * @param int $to_uid [收款方uid]
     * @param decimal $money [转账金额]
     * @return json
     */
    public function checkPwd()
    {

        if(!isPost()){
            return message(0, '非法提交');
        }
        $password = input('pwd/d');
        $money = input('money');
        $money = abs($money); // 防止提交负数
        $key = input('key/s');
        $to_uid = input('to_uid/d');
        if($key != $this->key){
            return message(0,'错误参数-key');
        }
        $user = Db::name('users')->field('id,account,password,pay_pwd,salt')->where('id',session('user.id'))->find();
        if(!$user){
            return message(0, '用户不存在');
        }
        // 判断接收用户是否存在
        $to_user = Db::name('users')->field('id')->where('id',$to_uid)->find();
        if(!$to_user){
            return message(0, '对方用户不存在');
        }
        // 判断是否属于好友关系
        $is_friends = getAllFriends($user['id']);
        $check_touid_in = deep_in_array($to_uid, $is_friends);
        if(!$check_touid_in){
            return message(0,'不属于好友关系');
        }
        if(!is_numeric($money)){
            return message(0,'金额有误');
        }
        if(!$money){
            return message(0,'金额必须大于0');
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
        if(!$user['pay_pwd']){
            return message(101, '请先设置支付密码');

        }
        if($user['pay_pwd'] !== minishop_md5($password, $user['salt'])){
            return message(0, '密码错误');
        }
        // 转账数据入库
        $time = time();
        $order_no = createOrderNo();
        $from_data = [
            'from_uid' => session('user.id'),
            'get_uid' => $to_uid,
            'order_no' => $order_no,
            'flag' => '-',
            'money' => $money,
            'create_time' => $time,
            'type' => 1 // 转出
        ];
        $to_data = [
            'from_uid' => session('user.id'),
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
            $to_account = Db::name('users')->field('id,account')->where('id', $to_uid)->find(); // 对方余额
            // 提交事务
            Db::commit();
            return message(1, '转账成功',['account'=>$user_account['account'], 'to_account'=>$to_account['account']]);
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return message(0, '网络异常,稍后再试');
        }

    }

    /**
     * [转账记录获取]
     * @return array
     */
    public function transferList()
    {   
        $list1 = Db::name('user_transfer')->where(['from_uid'=>session('user.id'), 'type'=>1])->order('create_time')->select();  // 转入
        $list2 = Db::name('user_transfer')->where(['get_uid'=>session('user.id'), 'type'=>2])->order('create_time')->select(); // 转出
        $list = array_merge($list1,$list2);
        $income_total = 0; // 收入
        $expend_total = 0; // 支出
        $now_date = date('Y年m月');
        foreach ($list as $k => $v) {
            if($v['type']==2){
                $expend_total += $v['money'];
                $from_user = Db::name('users')->field('nickname,head_imgurl')->where(['id'=>$v['from_uid']])->find();
            }else{
                $income_total += $v['money'];
                $from_user = Db::name('users')->field('nickname,head_imgurl')->where(['id'=>$v['get_uid']])->find();
            }
            $list[$k]['create_time'] = date('m月d日 H:i',$v['create_time']);
            
            $list[$k]['username'] = $from_user['nickname'];
            $list[$k]['head_imgurl'] = $from_user['head_imgurl'];
        }
        arsort($list);
        $this->assign('income_total', $income_total);
        $this->assign('expend_total', $expend_total);
        $this->assign('now_date', $now_date);
        $this->assign('list', $list);

        return $this->fetch('transferList');
    }


    /**
     * [群聊页面]
     * @return [type] [description]
     */
    public function groupChat()
    {   
        if( !is_complete(session('user.id')) ){
            $this->redirect('/index/my/personInfo');    
        };
        // $group_name = input('group_name/s');   // 群名称
        $fromid = input('fromid/d');           // 当前用户
        $roomid = input('room_id/d');           // 群id
        // 获取群id是否存在
        $group_one = Db::name('chat_group')->field('id,name,min_money,max_money')->where(['id'=>$roomid])->find();
        if(!$group_one){
            $this->redirect('/index/message/messageList');
            exit;
        }
        $user = Db::name('users')->field('id,nickname,account,head_imgurl')->where(['id'=>$fromid])->find();
        if(!$user){
            $this->redirect('/index/message/messageList');
            exit;
        }
        // 获取配置信息到页面
        $map['name'] = array('in', 'thunder_7_one,thunder_9_one,thunder_9_two,thunder_9_three,thunder_9_four,thunder_9_five');
        $rule_set = Db::name('setting')->field('name,value')->where($map)->select();
        $rule_set = arr2name($rule_set);
        
        // 获取群主信息到页面
        $group_owner = Db::name('users')->field('id,nickname,type')->where(['type'=>200])->find();
        $group_owner_chat_url = '/index/message/onetoonedialog?fromid='.$fromid.'&toid='.$group_owner['id'];
        $this->assign('group_owner_chat_url', $group_owner_chat_url);
        $this->assign('rule_set', $rule_set);
        $this->assign('user', $user);
        $this->assign('data', $group_one);
        $this->assign('fromid', $fromid);
        $this->assign('key', $this->key);
        $this->assign('send_key',$this->send_key);
        return $this->fetch('groupChat');
    }

    /**
     *文本消息的数据持久化
     */
    public function save_message(){
        
        if(!Request::instance()->isAjax()){
            return json(['code'=>0, 'msg'=>'非法请求', 'data'=>'']);
        }
        $message = input('post.');
        // 处理客户端发来的消息 data(文本类型消息)
        $text   = nl2br(htmlspecialchars($message['data']));
        // $text = preg_replace('/($s*$)|(^s*^)/m', '',$text);  

        $fromid = intval($message['fromid']);
        $toid   = intval($message['toid']);
        $send_type = $message['type']=='say'?1:2;
        if($message['type']=='say'){
            $send_type = 1;
        }else if($message['type']=='say_img'){
            $send_type = 2;
        }else if($message['type']=='transfer'){
            $send_type = 3;
        }else{
            $send_type = 1; // 否则1文本
        }

        // 判断当前session是否和发送者的uid一致
        if($fromid == session('user.id')){
            $datas['fromid'] = $fromid;
            $datas['from_name'] = $this->getName($fromid);
            $datas['toid'] = $toid;
            $datas['to_name'] = $this->getName($toid);
            $datas['content'] = $text;
            $datas['time'] = time();
            // $datas['is_read'] = $message['is_read'];
            $datas['type'] = $send_type;
            $res = Db::name("chat_info")->insert($datas);
            if(!$res){
                return json(['code'=>0, 'msg'=>'failed', 'data'=>'']);
            }
        }
        return json(['code'=>1, 'msg'=>'save ok', 'data'=>'']); 
    }

    /**
     * 信息入库时根据用户id返回用户昵称
     */
    protected function getName($uid){

        $userinfo = Db::name("users")->where('id',$uid)->field('nickname')->find();
        return $userinfo['nickname'];
    }

    /**
     * 检查交易密码是否设置
     */
    public function cehckPayPassword(){

        $user_arr = Db::name('users')->field('id,pay_pwd')->where('id',session('user.id'))->find();
        if(!$user_arr['pay_pwd']){
            return json(['code'=>0, 'msg'=>'请先设置支付密码', 'data'=>'']);
        }
        return json(['code'=>1, 'msg'=>'ok', 'data'=>'']);
    }

}
