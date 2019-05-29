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
        $user = Db::name('users')->field('id,account,password,pay_pwd,salt')->where('id',session('user.id'))->find();
        if(!$user){
            return message(0, '用户不存在');
        }
        if(!$room_id){
            return message(0,'异常,稍后再试-00');
        }
        $group_one = Db::name('chat_group')->field('id,min_money,max_money,status')->where('id',$room_id)->find();
        if(!$group_one){
            return message(0,'异常,稍后再试-01');
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
        // 根据群判断金额范围
        if($red_money < $group_one['min_money'] || $red_money > $group_one['max_money']){
            return message(0,'红包范围'.$group_one['min_money'].'-'.$group_one['max_money']);
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
        if(!$user['pay_pwd']){
            return message(0, '请先设置支付密码');
        }
        if($user['pay_pwd'] !== minishop_md5($password, $user['salt'])){
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
                'ray_point_num' => $ray_point_num,
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

            $robot_ones1 = $red_num==7?3:5;
            $robot_ones2 = $red_num==7?4:6;
            foreach($red_list['redMoneyList'] as $v){
                $detail_data = [
                    'm_id' => $res_id,
                    'money' => $v
                ];
                // 1号机器人获取红包
                if($robot_dis == $robot_ones1){ // 第3个红包
                // 平台两个机器人获得红包处理
                $detail_data['get_uid'] = 112; // 1号机器人uid
                $detail_data['get_time'] = time();
                $detail_data['type'] = 1;
                // $robot_one = Db::name('chat_red_detail')->where(['m_id'=>$res_id])->update($robot_one_data)->limit(1);
                }
                // 2免死机器人
                if($robot_dis == $robot_ones2){ //第5个红包
                    $detail_data['get_uid'] = 113; // 2号机器人uid 免死
                    $detail_data['get_time'] = time();
                    $detail_data['type'] = 1;
                    $detail_data['is_die'] = 1; // 免死标记

                    // 免死金额5%返到发包用户
                    $rebate_money = $v;
                }
                // 根据设置雷点 标记好中雷的红包
                // $new_v = preg_replace("/[.]/",'',$v);
                // 转换为数组
                // $new_v_arr = str_split($new_v);
                // foreach ($ray_points as $v) {
                //     if(in_array($v, $new_v_arr)){
                //         $detail_data['is_die'] = 2; // 中雷
                //     }
                // }
                // 获取红包金额最后一位数
                $last_number = substr($v,-1);
                // 如果雷点数是1个时
                if($ray_point_num == 1){
                    if(in_array($last_number, $ray_points)){
                        $detail_data['is_ray'] = 2; // 中雷
                    }
                }
                // 如果雷点两个以上时,必须2个人尾数都中雷才算中雷
                if($ray_point_num > 1){
                    $ray_num = 0;
                    // 循环把所有红包金额尾数获取组装成数组
                    $red_list_last = [];
                    foreach ($red_list['redMoneyList'] as $k=>$vs) {
                        $last_number = substr($vs,-1);
                        $red_list_last[$k] = $last_number;
                    }
                    // 判断雷点数和红包金额中雷数
                    foreach ($ray_points as $vv) {
                        if(in_array($vv, $red_list_last)){
                            $ray_num++;
                        }
                    }
                    // 雷点数和中雷人数相等时中雷
                    if($ray_num == $ray_point_num){
                        $last_number_new = substr($v,-1);
                        if(in_array($last_number_new, $ray_points)){
                            $detail_data['is_ray'] = 2; // 中雷
                        }
                    }
                }

                
                $res = Db::name('chat_red_detail')->insert($detail_data);
                $robot_dis++;
            }

            // +-------------------发包返水-------------------+
            $upAll_arr = getUpMemberIds($user['id']); // 获取所有上级最多30级
            unset($GLOBALS['g_up_mids']); // 清空上一次循环全局数据
            unset($GLOBALS['up_i']); 
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
                            'from_id' => $user['id'],
                            'uid' => $v,
                            'm_id' => $res_id,
                            'red_money' => $red_money,
                            'rebate' => $superior_rebate,
                            'money' => $superior_rebate_money,
                            'type' => 4,
                            'user_level' => $keys, // 等级
                            'create_time' => $time,
                            'remake' => '发包返水'
                        ];
                        $superior_red_log_res = Db::name('chat_red_log')->insert($superior_red_log);
                    }
                }
            }

            // 发包返免死金额的5%到发包用户
            $no_die_money = Db::name('chat_red_detail')->where(['m_id'=>$res_id, 'is_die'=>1])->find();
            $rebate_money  = $no_die_money['money']*(abs(5/100));
            $rebate_res = Db::name('users')->where(['id'=>$user['id']])->setInc('account', $rebate_money);
            $red_rebate_data = [
                'from_id' => $user['id'],
                'uid' => $user['id'],
                'm_id' => $res_id,
                'red_money' => $red_money,
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
                'money' => '-'.$red_money,
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

    /**
     * 用户点击抢红包
     * @param int m_id 红包id
     */
    public function openToRed()
    {
        if(!isPost()){
            return message(0, '非法提交');
        }
        $room_id = input('room_id/d');
        $m_id = input('red_id/d');
        $key = input('key/s');
        if($key != $this->key){
            return message(0,'错误参数-key');
        }
        if(!$room_id || !$m_id){
            return message(0,'缺少参数-');
        }
        $user = Db::name('users')->field('id,account,password,pay_pwd,salt')->where('id',session('user.id'))->find();
        if(!$user){
            return message(0, '用户不存在');
        }
        $group_one = Db::name('chat_group')->field('id,min_money,max_money,status')->where('id',$room_id)->find();
        if(!$group_one){
            return message(0,'异常,稍后再试-01');
        }
        $red_one = Db::name('chat_red_master')->where('id',$m_id)->find();
        if(!$red_one){
            return message(0,'异常,稍后再试-02');
        }
        // 判断用户是否有足够的金额，包括中雷赔付
        if($red_one['ray_point'] && $red_one['mulriple']){
            $compensate_money = $red_one['money']*$red_one['mulriple'];
            if($user['account'] < $compensate_money){
                return message(0,'余额不足-01');   
            }
        }else{
            if($user['account'] < $red_one['money']){
                return message(0,'余额不足-02');
            }
        }

        $is_get = Db::name('chat_red_detail')->where(['m_id'=>$red_one['id'], 'get_uid'=>$user['id'], 'type'=>1])->find();
        if($is_get){
            $data = $this->getRedDetail2($is_get['m_id']);
            $data['master_info']['is_die_flag'] =  $is_get['is_ray']==2?'你已中雷':'你未中雷';
            $data['master_info']['get_award_money'] =  $is_get['get_award_money']>0?$is_get['get_award_money']:0;
            $data['master_info']['get_red_money'] = $is_get['money'];
            return message(101,'红包已抢过', $data);
        }
        // 判断红包是否过期 大于5分钟
        if(!$is_get){
            $ex_time = $red_one['create_time']+(5*60);
            if(time() > $ex_time){
                return message(-1,'红包已过期');
            }
        }

        // 启动事务
        Db::startTrans();
        try{
            // 获取一个红包记录
            $red_detail = Db::name('chat_red_detail')->where(['m_id'=>$red_one['id'], 'get_uid'=>0, 'type'=>0])->lock(true)->find();
            
            $time = time();
            if(!$red_detail['money']){
                return message(0,'红包异常-00');
            }

            // 增加抢到的红包金额到对应用户
            $get_red_res = Db::name('users')->where(['id'=>$user['id']])->setInc('account', $red_detail['money']);
            // 更改红包记录状态
            $get_update = [
                'get_uid' => $user['id'],
                'get_time' => $time,
                'type' => 1
            ];
            $get_update_res = Db::name('chat_red_detail')->where(['id'=>$red_detail['id']])->update($get_update);
            // 插入抢到红包记录
            $grab_red_log = [
                'from_id' => $red_one['uid'],
                'uid' => $user['id'],
                'm_id' => $red_one['id'],
                'red_money' => $red_one['money'],
                'money' => $red_detail['money'],
                'type' => 11,
                'create_time' => $time,
                'remake' => '抢到红包'
            ];
            $grab_red_log_res = Db::name('chat_red_log')->insert($grab_red_log);

            // 抢包返水
            $upAll_arr = getUpMemberIds($user['id']); // 获取所有上级最多30级
            unset($GLOBALS['g_up_mids']); // 清空上一次循环全局数据
            unset($GLOBALS['up_i']); 
            $superior_rebate_money_res = true;
            $superior_rebate_money_log_res = true;
            $rule_set = Db::name('setting')->field('name,value')->select();
            $rule_set = arr2name($rule_set);
            if($upAll_arr){

                foreach($upAll_arr as $k=>$v){
                    $keys = $k+1;
                    // 给上级返水金额
                    if($keys >= 1 && $keys <= 6){
                        $superior_rebate_money = $rule_set['rob_between_1_6']['value'];
                    }else if($keys >= 7 && $keys <= 10){
                        $superior_rebate_money = $rule_set['rob_between_7_10']['value'];
                    }else if($keys >= 11 && $keys <= 15){
                        $superior_rebate_money = $rule_set['rob_between_11_15']['value'];
                    }else if($keys >= 16 && $keys <= 20){
                        $superior_rebate_money = $rule_set['rob_between_16_20']['value'];
                    }else if($keys >= 21 && $keys <= 25){
                        $superior_rebate_money = $rule_set['rob_between_21_25']['value'];
                    }else if($keys >= 26 && $keys <= 30){
                        $superior_rebate_money = $rule_set['rob_between_26_30']['value'];
                    }else{
                        $superior_rebate_money = 0;
                    }
                    if($superior_rebate_money){
                        $superior_rebate_money = abs($superior_rebate_money);
                        $superior_rebate_money_res = Db::name('users')->where(['id'=>$v])->setInc('account', $superior_rebate_money);

                        // 发包返水插入chat_red_log流水日志
                        $superior_rebate_money_log = [
                            'from_id' => $red_one['uid'],
                            'uid' => $v,
                            'm_id' => $red_one['id'],
                            'red_money' => $red_one['money'],
                            'money' => $superior_rebate_money,
                            'type' => 6,
                            'user_level' => $keys, // 等级
                            'create_time' => $time,
                            'remake' => '抢包返水'
                        ];
                        $superior_rebate_money_log_res = Db::name('chat_red_log')->insert($superior_rebate_money_log);
                    }
                }
            }
            // +-------------奖励-------------+
            // 抢包奖励 抢到指定金额
            $award_money = $this->awardList($red_detail['money']);
            $award_log_res = true;
            $award_rebate_res =true;
            if($award_money){
                // 累加奖励金额
                $award_rebate_res = Db::name('users')->where(['id'=>$user['id']])->setInc('account', $award_money);
                // 抢包奖励插入chat_red_log流水日志
                $award_log = [
                    'from_id' => $red_one['uid'],
                    'uid' => $user['id'],
                    'm_id' => $red_one['id'],
                    'red_money' => $red_one['money'],
                    'con_money' => $red_detail['money'],
                    'money' => $award_money,
                    'type' => 7,
                    'create_time' => $time,
                    'remake' => '抢包奖励'
                ];
                $award_log_res = Db::name('chat_red_log')->insert($award_log);
            }

            // 判断当前红包主表是否记录 发包奖励 返给发包人
            $point_award_money_res = true;
            $point_award_money_log_res = true;
            $point_award_update_res = true;
            $award_flag_res = true;
            if(!$red_one['is_award']){
                // 7包发包奖励 
                // 获取红包明细，中雷人数是否和雷点个数一致
                $where['m_id'] = $red_one['id'];
                $where['type'] = 1;
                $where['is_ray'] = 2;
                $where['get_uid'] = ['>', 0]; 
                $where['is_die'] = ['=', 0]; // 不含免死

                $get_detail_point = Db::name('chat_red_detail')->where($where)->count();
                if($red_one['num'] == 7){
                    if($get_detail_point == 3){
                        $point_award_money = $rule_set['pack7_3']['value'];
                    }else if($get_detail_point == 4){
                        $point_award_money = $rule_set['pack7_4']['value'];
                    }else if($get_detail_point == 5){
                        $point_award_money = $rule_set['pack7_5']['value'];
                    }else if($get_detail_point == 6){
                        $point_award_money = $rule_set['pack7_6']['value'];
                    }else{
                        $point_award_money = 0;
                    }

                }else{
                    // 9包发包奖励（50以下没奖励）单雷奖 多雷奖
                    // 判断是否大于50 不包含免死
                    if($red_one['money'] >= 50){

                        // 单雷奖
                        if($red_one['ray_point_num'] == 1){
                            if($get_detail_point == 4){
                                $point_award_money = $rule_set['pack9_4_one']['value'];
                            }else if($get_detail_point == 5){
                                $point_award_money = $rule_set['pack9_5_one']['value'];
                            }else if($get_detail_point == 6){
                                $point_award_money = $rule_set['pack9_6_one']['value'];
                            }else if($get_detail_point == 7){
                                $point_award_money = $rule_set['pack9_7_one']['value'];
                            }else{
                                $point_award_money = 0;
                            }
                        }else if($red_one['ray_point_num'] > 1){
                            if($get_detail_point == 6){
                                $point_award_money = $rule_set['pack9_6_two']['value'];
                            }else if($get_detail_point == 7){
                                $point_award_money = $rule_set['pack9_7_two']['value'];
                            }else if($get_detail_point == 8){
                                $point_award_money = $rule_set['pack9_8_two']['value'];
                            }else if($get_detail_point == 9){
                                $point_award_money = $rule_set['pack9_9_two']['value'];
                            }else{
                                $point_award_money = 0;
                            }
                        }else{
                            $point_award_money = 0;
                        }
                    }else{
                        $point_award_money = 0;
                    }
                }

                // 累加奖励并记录 
                if($point_award_money){
                    $point_award_money = abs($point_award_money);
                    $point_award_money_res = Db::name('users')->where(['id'=>$red_one['uid']])->setInc('account', $point_award_money);
                    // 修改红包主表标记已奖励
                    $point_award_update_res = Db::name('chat_red_master')->where(['id'=>$red_one['id']])->update(['is_award'=>1]);
                    $point_award_money_log = [
                        'from_id' => $red_one['uid'],
                        'uid' => $red_one['uid'],
                        'm_id' => $red_one['id'],
                        'red_money' => $red_one['money'],
                        'money' => $point_award_money,
                        'type' => 8,
                        'create_time' => $time,
                        'remake' => '发包奖励'
                    ];
                    $point_award_money_log_res = Db::name('chat_red_log')->insert($point_award_money_log);

                    // 抢到当前红包获得奖励金额标记
                    $award_flag_res = Db::name('chat_red_detail')->where(['id'=>$red_detail['id']])->update(['get_award_money'=>$point_award_money]);
                }
            }
            // 抢包返利 系统返利
            $system_rebate_money = 0.05;
            $system_rebate_res = Db::name('users')->where(['id'=>$user['id']])->setInc('account', $system_rebate_money);
            $system_rebate_log = [
                'from_id' => $red_one['uid'],
                'uid' => $user['id'],
                'm_id' => $red_one['id'],
                'red_money' => $red_one['money'],
                'money' => $system_rebate_money,
                'type' => 9,
                'create_time' => $time,
                'remake' => '系统返利'
            ];
            $system_rebate_log_res = Db::name('chat_red_log')->insert($system_rebate_log);

            // 中雷 如果中雷是发包本人不作处理
            $dec_res = true;
            $dec_log_res = true;
            if($red_detail['is_ray'] == 2 && $user['id'] != $red_one['uid']){

                // 扣除金额=红包本金*赔率
                $dec_money = $red_one['money']*$red_one['mulriple'];
                $dec_res = Db::name('users')->where(['id'=>$user['id']])->setDec('account', $dec_money);
                $dec_log = [
                    'from_id' => $red_one['uid'],
                    'uid' => $user['id'],
                    'm_id' => $red_one['id'],
                    'red_money' => $red_one['money'],
                    'money' => '-'.$dec_money,
                    'type' => 10,
                    'create_time' => $time,
                    'remake' => '中雷'
                ];
                $dec_log_res = Db::name('chat_red_log')->insert($dec_log);
            }
            // 获取发包者的信息
            $from_user = Db::name('users')->field('id,nickname,head_imgurl')->where('id',$red_one['uid'])->find();

            Db::commit();
            $data = [
                'get_red_money' => $red_detail['money'],
                'is_die_flag' => $red_detail['is_ray']==2?'你已中雷':'你未中雷',
                'red_id' => $red_one['id'],
                'from_id' => $from_user['id'],
                'from_name' => $from_user['nickname'],
                'from_head' => $from_user['head_imgurl'],
                'get_award_money' => $point_award_money?$point_award_money:0
            ];
            return message(1, 'ok', $data);
        }catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return message(0, '网络异常,稍后再试');
        }
    }

    /**
     * 获取红包详情
     * @param int $red_id 红包id
     */
    public function getRedDetail()
    {
        if(!isPost()){
            return message(0, '非法提交');
        }
        $m_id = input('red_id/d');
        $key = input('key/s');
        if($key != $this->key){
            return message(0,'错误参数-key');
        }
        if(!$m_id){
            return message(0,'缺少参数-');
        }
        $master_info = Db::name('chat_red_master')->field('id,uid,room_id,num,money,ray_point')->where(['id'=>$m_id])->find();
        if(!$master_info){
            return message(0,'红包不存在');
        }
        $master_user = Db::name('users')->field('id,nickname,head_imgurl')->where(['id'=>$master_info['uid']])->find();
        $detail_info = Db::name('chat_red_detail')->alias('d')
                        ->field('d.id,d.m_id,d.get_uid,d.money,d.get_time,d.type,d.is_die,d.is_ray,d.get_award_money,u.nickname,u.head_imgurl')
                        ->join('users u','d.get_uid = u.id')
                        ->where(['d.type'=>1, 'd.m_id'=>$m_id])
                        ->order('get_time desc')
                        ->select();
        $master_info['get_num'] = count($detail_info);
        $master_info['nickname'] = $master_user['nickname'];
        $master_info['head_imgurl'] = $master_user['head_imgurl'];
        foreach($detail_info as $k=>$v){
            if($v['get_uid']==113){ // 免死机器人
                $detail_info[$k]['nickname'] = '免死金牌';
            }
            if($v['get_uid']==112){ // 平台抢红包机器人
                $detail_info[$k]['nickname'] = '平台';
            }
            $detail_info[$k]['get_time_date'] = date('Y-m-d',$v['get_time']);
            $detail_info[$k]['get_time'] = date('H:i:s',$v['get_time']);
        }
        
        $data = [
            'master_info' => $master_info,
            'detail_info' => $detail_info
        ];
        return message(1,'ok', $data);
    }
    
    // 群设置
    public function groupSet()
    {
        return $this->fetch('/message/group_set');
    }

    // 抢包奖励，抢到对应金额
    private function awardList($get_red_money)
    {
        if(!$get_red_money){
            return false;
        }
        $rule_set = Db::name('setting')->field('name,value')->where(['flag'=>4])->select();
        $rule_set = arr2name($rule_set);
        $award1 = ['1' => 1.23,'2' => 2.34,'3' => 3.45,'4' => 5.67,'5' => 6.78,'6' => 7.89];
        $award2 = ['1'=>11.11,'2'=>22.22,'3'=>33.33,'4'=>44.44,'5'=>55.55];
        $award3 = ['1'=>9.87,'2'=>8.76,'3'=>7.65,'4'=>6.54,'5'=>5.43,'6'=>4.32,'7'=>3.21];
        $award4 = ['1'=>66.66,'2'=>77.77,'3'=>88.88,'4'=>99.99];
        $award5 = ['1'=>1.11,'2'=>2.22,'3'=>3.33,'4'=>4.44,'5'=>5.55,'6'=>6.66,'7'=>7.77,'8'=>8.88,'9'=>9.99];
        $award6 = ['1' =>12.34,'2' =>23.45,'3' =>34.56,'4' =>45.67,'5' =>56.78,'6' =>67.89];
        $award7 = ['1' =>5.20];
        $award8 = ['1' => 13.14];
        $award9 = ['1' =>0.01];
        $award10 = ['1' =>123.45,'2' =>234.56,'3' =>345.67];
        $award11 = ['1' =>111.11,'2' =>222.22,'3' =>333.33,'4' =>444.44];
        if(in_array($get_red_money, $award1)){
            $money = $rule_set['robbery_m1_23to7_89']['value'];
            return abs($money);
        }else if(in_array($get_red_money, $award2)){
            $money = $rule_set['robbery_m11_11to55_55']['value'];
            return abs($money);
        }else if(in_array($get_red_money, $award3)){
            $money = $rule_set['robbery_m9_87to3_21']['value'];
            return abs($money);
        }else if(in_array($get_red_money, $award4)){
            $money = $rule_set['robbery_m66_66to99_99']['value'];
            return abs($money);
        }else if(in_array($get_red_money, $award5)){
            $money = $rule_set['robbery_m1_11to9_99']['value'];
            return abs($money);
        }else if(in_array($get_red_money, $award6)){
            $money = $rule_set['robbery_m12_34to67_89']['value'];
            return abs($money);
        }else if(in_array($get_red_money, $award7)){
            $money = $rule_set['robbery_m5_20']['value'];
            return abs($money);
        }else if(in_array($get_red_money, $award8)){
            $money = $rule_set['robbery_m13_14']['value'];
            return abs($money);
        }else if(in_array($get_red_money, $award9)){
            $money = $rule_set['robbery_m001']['value'];
            return abs($money);
        }else if(in_array($get_red_money, $award10)){
            $money = $rule_set['robbery_m123_45to345_56']['value'];
            return abs($money);
        }else if(in_array($get_red_money, $award11)){
            $money = $rule_set['robbery_m111_11to444_44']['value'];
            return abs($money);
        }else{
            return false;
        }
        
    }


    // 红包详情2 
    private function getRedDetail2($red_id)
    {
  
        $m_id = $red_id;
        $master_info = Db::name('chat_red_master')->field('id,uid,room_id,num,money,ray_point')->where(['id'=>$m_id])->find();
        if(!$master_info){
            return message(0,'红包不存在');
        }
        $master_user = Db::name('users')->field('id,nickname,head_imgurl')->where(['id'=>$master_info['uid']])->find();
        $detail_info = Db::name('chat_red_detail')->alias('d')
                        ->field('d.id,d.m_id,d.get_uid,d.money,d.get_time,d.type,d.is_die,d.is_ray,d.get_award_money,u.nickname,u.head_imgurl')
                        ->join('users u','d.get_uid = u.id')
                        ->where(['d.type'=>1, 'd.m_id'=>$m_id])
                        ->order('get_time desc')
                        ->select();
        $master_info['get_num'] = count($detail_info);
        $master_info['nickname'] = $master_user['nickname'];
        $master_info['head_imgurl'] = $master_user['head_imgurl'];
       
        foreach($detail_info as $k=>$v){
            if($v['get_uid']==113){ // 免死机器人
                $detail_info[$k]['nickname'] = '免死金牌';
            }
            if($v['get_uid']==112){ // 平台抢红包机器人
                $detail_info[$k]['nickname'] = '老东家888';
            }
            $detail_info[$k]['get_time_date'] = date('Y-m-d',$v['get_time']);
            $detail_info[$k]['get_time'] = date('H:i:s',$v['get_time']);
        }
        
        $data = [
            'master_info' => $master_info,
            'detail_info' => $detail_info
        ];
        return $data;
    }
    
}
