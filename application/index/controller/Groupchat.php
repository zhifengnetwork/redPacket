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
        if($red_num<7 || $red_num>9){
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
                $map['name'] = array('in', 'thunder_7_one,thunder_9_one,thunder_9_two,thunder_9_three,thunder_9_four,thunder_9_five');
                $rule_set = Db::name('setting')->field('name,value')->where($map)->select();
                $rule_set = arr2name($rule_set);
                if($red_num==7){
                  $master_data['mulriple'] = $rule_set['thunder_7_one']['value'];
                }else{
                    if($ray_point_num == 1){
                        $master_data['mulriple'] = $rule_set['thunder_9_one']['value'];
                    }else if($ray_point_num == 2){
                        $master_data['mulriple'] = $rule_set['thunder_9_two']['value'];
                    }else if($ray_point_num == 3){
                        $master_data['mulriple'] = $rule_set['thunder_9_three']['value'];
                    }else if($ray_point_num == 4){
                        $master_data['mulriple'] = $rule_set['thunder_9_four']['value'];
                    }else if($ray_point_num == 5){
                        $master_data['mulriple'] = $rule_set['thunder_9_five']['value'];
                    }else{
                        $master_data['mulriple'] = 0;
                    }
                }
            }
            $res_id = Db::name('chat_red_master')->insertGetId($master_data);
            // 循环插入红包记录到从表
            $robot_dis = 1;
            foreach($red_list['redMoneyList'] as $v){
                $detail_data = [
                    'm_id' => $res_id,
                    'money' => $v
                ];
                // 1号机器人获取红包
                if($robot_dis == 3){ // 第3个红包
                    // 平台两个机器人获得红包处理
                    $detail_data['get_uid'] = 112; // 1号机器人uid
                    $detail_data['get_time'] = time();
                    $detail_data['type'] = 1;
                    // $robot_one = Db::name('chat_red_detail')->where(['m_id'=>$res_id])->update($robot_one_data)->limit(1);
                    
                }
                // 2免死机器人
                if($robot_dis == 4){ //第5个红包
                    $detail_data['get_uid'] = 113; // 2号机器人uid 免死
                    $detail_data['get_time'] = time();
                    $detail_data['type'] = 1;
                    $detail_data['is_die'] = 1;

                    // 免死金额5%返到发包用户
                    $rebate_money = $v;
                }

                // 根据设置雷点 标记好中雷的红包
                $new_v = preg_replace("/[.]/",'',$v);
                // 转换为数组
                $new_v_arr = str_split($new_v);
                foreach ($ray_points as $v) {
                    if(in_array($v, $new_v_arr)){
                        $detail_data['is_die'] = 2; // 中雷
                    }
                }
                
                $res = Db::name('chat_red_detail')->insert($detail_data);
                $robot_dis++;
            }

            // +-------------------发包返水-------------------+
            $upAll_arr = getUpMemberIds($user['id']); // 获取所有上级最多30级
            unset($GLOBALS['g_up_mids']); // 清空上一次循环全局数据
            unset($GLOBALS['up_i']); 
            // pre($upAll_arr);die;
            $upAll_i = 1;
            $superior_red_log_res = true;
            $superior_rebate_res = true;
            if($upAll_arr){
                $rule_set = Db::name('setting')->field('name,value')->where(['flag'=>1])->select();
                $rule_set = arr2name($rule_set);

                foreach ($upAll_arr as $k => $v) {
                    $keys = $k+1;
                    if($keys == 1){
                        $superior_rebate = $rule_set['out_one']['value'];
                    }else if($keys == 2){
                        $superior_rebate = $rule_set['out_two']['value'];
                    }else if($keys == 3){
                        $superior_rebate = $rule_set['out_three']['value'];
                    }else if($keys == 4){
                        $superior_rebate = $rule_set['out_four']['value'];
                    }else if($keys == 5){
                        $superior_rebate = $rule_set['out_five']['value'];
                    }else if($keys == 6){
                        $superior_rebate = $rule_set['out_six']['value'];
                    }else if($keys >= 7 && $keys <= 10){
                        $superior_rebate = $rule_set['out_between_7_10']['value'];
                    }else if($keys >= 11 && $keys <= 15){
                        $superior_rebate = $rule_set['out_between_11_15']['value'];
                    }else if($keys >= 16 && $keys <= 20){
                        $superior_rebate = $rule_set['out_between_16_20']['value'];
                    }else if($keys >= 21 && $keys <= 25){
                        $superior_rebate = $rule_set['out_between_21_25']['value'];
                    }else if($keys >= 26 && $keys <= 30){
                        $superior_rebate = $rule_set['out_between_26_30']['value'];
                    }else{
                        $superior_rebate = 0;
                    }
                    if($superior_rebate){
                        $superior_rebate_money = $red_money*(abs($superior_rebate/100));
                        $superior_rebate_res = Db::name('users')->where(['id'=>$v])->setInc('account', $superior_rebate_money);

                        // 发包返水插入chat_red_log流水日志
                        $superior_red_log = [
                            'uid' => $v,
                            'm_id' => $res_id,
                            'red_money' => $red_money,
                            'rebate' => $superior_rebate,
                            'money' => $superior_rebate_money,
                            'type' => 4,
                            'create_time' => $time,
                            'remake' => '发包返水'
                        ];
                        $superior_red_log_res = Db::name('chat_red_log')->insert($superior_red_log);
                    }
                }
            }

            // 发包返免死金额的5%到发包用户
            $rebate_money  = $rebate_money*(abs(5/100));
            $rebate_res = Db::name('users')->where(['id'=>$user['id']])->setInc('account', $rebate_money);
            $red_rebate_data = [
                'from_id' => $user['id'],
                'uid' => $user['id'],
                'm_id' => $res_id,
                'money' => $rebate_money,
                'type' => 3,
                'create_time' => $time,
                'remake' => '发包返免死金额的5%'
            ];
            $rebate_in = Db::name('chat_red_log')->insert($red_rebate_data);

            // 插入chat_red_log流水日志
            $red_log = [
                'uid' => $user['id'],
                'm_id' => $res_id,
                'money' => $red_money,
                'type' => 1,
                'create_time' => $time,
                'remake' => '发包'
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
