<?php
namespace app\index\controller;
use think\Db;
use think\Config;
// 群相关
class Groupchat extends Base
{
    private $key;
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
     * [群发红包处理]
     * @param int $red_num [红包个数]
     * @param int $red_money [红包金额]
     * @param array $ray_point [雷点]
     * @param string $pwd [密码]
     * @return json
     */
    public function groupSendRed()
    {
        if(!isPost()){
            return message(0, '非法提交');
        }
        $room_id = input('room_id/d');
        $red_num = input('red_num/d');
        $red_money = input('red_money');
        $ray_points = input('ray_point/a'); // 雷点
        $password = input('pwd/s');
        $red_money = abs($red_money); // 防止提交负数
        $key = input('key/s');
        $ray_point_num = count($ray_points); // 雷点数量
        $ray_point = '';
        if($ray_point_num){
            foreach ($ray_points as $v) {
                $ray_point .= $v.',';
            }
            $ray_point = rtrim($ray_point, ','); // 最终所有雷点1,2,3
        }
        
        if($key != $this->key){
            return message(0,'错误参数-key');
        }
        $user = Db::name('users')->field('id,account,password,salt')->where('id',session('user.id'))->find();
        if(!$user){
            return message(0, '用户不存在');
        }
        if(!$room_id){
            return message(0,'群内异常,稍后再试');
        }
        if(!$red_num){
            return message(0,'红包个数NULL');
        }
        if(!is_numeric($red_num)){
            return message(0,'红包个数有误');
        }
        if(!is_numeric($red_money)){
            return message(0,'金额有误');
        }
        if($red_money>$user['account']){
            return message(0,'余额不足');
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
        // 生成红包
        $red_list = createRedDate($red_money, $red_num);
        if(!$red_list){
            return message(0, '红包出错！');
        }
        // 启动事务
        Db::startTrans();
        try{
            // 扣减金额
            $res_dec = Db::name('users')->where(['id'=>$user['id']])->setDec('account',$red_money);
            $time = time();
            // 插入红包主表
            $master_data = [
                'uid' => $user['id'],
                'room_id' => $room_id,
                'num' => $red_num,
                'money' => $red_money,
                'create_time' => $time,
            ];
            if($ray_point){
                $master_data['ray_point'] = $ray_point;
                // 根据雷点获取倍数计算
                // $master_data['mulriple'] = $mulriple;
            }
            $res_id = Db::name('chat_red_master')->insertGetId($master_data);
            // 循环插入红包记录到从表
            foreach($red_list['redMoneyList'] as $v){
                $detail_data = [
                    'm_id' => $res_id,
                    'money' => $v
                ];
                $res = Db::name('chat_red_detail')->insert($detail_data);
            }

            // 平台两个机器人获得处理


            // +-------------------发包返水-------------------+
            
            $upAll_arr = getUpMemberIds($user['id']); // 获取所有上级最多30级
            unset($GLOBALS['g_up_mids']); // 清空上一次循环全局数据
            unset($GLOBALS['up_i']); 
            // pre($upAll_arr);die;
            if($upAll_arr){

            }

            // 发包返利免死金额的5%

        

            // 插入chat_red_log流水日志
            $red_log = [
                'uid' => $user['id'],
                'm_id' => $res_id,
                'money' => $red_money,
                'type' => 1,
                'create_time' => $time
            ];
            $res2 = Db::name('chat_red_log')->insert($red_log);
            Db::commit();  
            return message(1, 'ok', ['red_id'=>$res_id]);
        }catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return message(0, '网络异常,稍后再试');
        }
    }
    
}
