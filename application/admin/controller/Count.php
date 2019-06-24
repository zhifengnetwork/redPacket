<?php

/**
 * 统计管理
 */
namespace app\admin\controller;

use think\Db;
use think\Session;
use think\Request;
class Count extends Common
{
    # 统计列表
    public function countList(){

        // 发包总金额
        $send_red_total_money = Db::name('chat_red_master')->sum('money');
        // 今天发包总金额
        $today_send_red_money = Db::name('chat_red_master')->whereTime('create_time', 'today')->sum('money');
        // 本月发包总额
        $month_send_red_money = Db::name('chat_red_master')->whereTime('create_time', 'month')->sum('money');
        
        // 超时退回总额
        $out_time_money = Db::name('chat_red_detail')->where('type',2)->sum('money');
        // 今天超时退回总额
        $today_out_time_money = Db::name('chat_red_detail')->where('type',2)->whereTime('out_time', 'today')->sum('money');
        // 本月超时退回总额
        $month_out_time_money = Db::name('chat_red_detail')->where('type',2)->whereTime('out_time', 'month')->sum('money');
        
        // 抢包总金额
        $get_red_total_money = Db::name('chat_red_detail')->where('type',1)->sum('money');
        // 今天抢包总金额
        $today_get_red_total_money = Db::name('chat_red_detail')->where('type',1)->whereTime('get_time', 'today')->sum('money');

        // 发包返利type=3
        $total_send_red_rebate = Db::name('chat_red_log')->where('type',3)->sum('money');
        $today_total_send_red_rebate = Db::name('chat_red_log')->where('type',3)->whereTime('create_time', 'today')->sum('money');
        // 发包返水type=4
        $total_send_red_backwater = Db::name('chat_red_log')->where('type',4)->sum('money');
        $today_total_send_red_backwater = Db::name('chat_red_log')->where('type',4)->whereTime('create_time', 'today')->sum('money');
        
        // 抢包返利type=5 【 抢完包 的 数量  乘以  0.05  】
        $total_get_red_rebate = Db::name('chat_red_detail')->where('type',1)->field('id')->count();
        $total_get_red_rebate = round($total_get_red_rebate * 0.05);

        $today_total_get_red_rebate = Db::name('chat_red_detail')->where('type',1)->whereTime('get_time', 'today')->field('id')->count();
        $today_total_get_red_rebate = round($today_total_get_red_rebate * 0.05);

        // 抢包返水type=6
        $total_get_red_backwater = Db::name('chat_red_log')->where('type',6)->sum('money');
        $today_total_get_red_backwater = Db::name('chat_red_log')->where('type',6)->whereTime('create_time', 'today')->sum('money');
        // 抢包奖励type=7
        $total_get_red_award = Db::name('chat_red_log')->where('type',7)->sum('money');
        $today_total_get_red_award = Db::name('chat_red_log')->where('type',7)->whereTime('create_time', 'today')->sum('money');
        // 发包奖励type=8
        $total_send_red_award = Db::name('chat_red_log')->where('type',8)->sum('money');
        $today_total_send_red_award = Db::name('chat_red_log')->where('type',8)->whereTime('create_time', 'today')->sum('money');
        // 系统返利type=9
        $total_system_rebate = Db::name('chat_red_log')->where('type',9)->sum('money');
        $today_total_system_rebate = Db::name('chat_red_log')->where('type',9)->whereTime('create_time', 'today')->sum('money');
        // 中雷type=10
        $total_ray = Db::name('chat_red_log')->where('type',10)->sum('money');
        $today_total_ray = Db::name('chat_red_log')->where('type',10)->whereTime('create_time', 'today')->sum('money');

        // 今日总免死金额返利
        $today_miansi = Db::name('chat_red_detail')->where('is_die',1)->whereTime('get_time', 'today')->sum('money');
        $today_miansi = round($today_miansi * 0.05,2);
        // 总免死金额返利
        $total_miansi_all = Db::name('chat_red_detail')->where('is_die',1)->sum('money');

        $total_miansi = round($total_miansi_all * 0.05,2);

        $this->assign('today_miansi', $today_miansi);
        $this->assign('total_miansi', $total_miansi);

        $this->assign('total_miansi_all', $total_miansi_all);


        $this->assign('send_red_total_money', $send_red_total_money);
        $this->assign('today_send_red_money', $today_send_red_money);
        $this->assign('month_send_red_money', $month_send_red_money);

        $this->assign('out_time_money', $out_time_money);
        $this->assign('today_out_time_money', $today_out_time_money);
        $this->assign('month_out_time_money', $month_out_time_money);

        $this->assign('get_red_total_money', $get_red_total_money);
        $this->assign('today_get_red_total_money', $today_get_red_total_money);

        $this->assign('total_send_red_rebate', $total_send_red_rebate);
        $this->assign('today_total_send_red_rebate', $today_total_send_red_rebate);
        $this->assign('total_send_red_backwater', $total_send_red_backwater);
        $this->assign('today_total_send_red_backwater', $today_total_send_red_backwater);
        $this->assign('total_get_red_rebate', $total_get_red_rebate);
        $this->assign('today_total_get_red_rebate', $today_total_get_red_rebate);
        $this->assign('total_get_red_backwater', $total_get_red_backwater);
        $this->assign('today_total_get_red_backwater', $today_total_get_red_backwater);
        $this->assign('total_get_red_award', $total_get_red_award);
        $this->assign('today_total_get_red_award', $today_total_get_red_award);
        $this->assign('total_send_red_award', $total_send_red_award);
        $this->assign('today_total_send_red_award', $today_total_send_red_award);
        $this->assign('total_system_rebate', $total_system_rebate);
        $this->assign('today_total_system_rebate', $today_total_system_rebate);
        $this->assign('total_ray', abs($total_ray));
        $this->assign('today_total_ray', abs($today_total_ray));


        //总返利金额
        // $total_fanli = 555555;
        // $this->assign('total_fanli', $total_fanli);

        if($_SERVER['HTTP_HOST'] == 'www.zxxhrj.cn'){
            $this->assign('is_show', 0);
        }else{           
           $this->assign('is_show', 1);
        }

        return $this->fetch('count');
    }

    /**
     * 级别查询统计充值以及提现页面
     */
    public function levelCount(){
        return $this->fetch('level_count');
    }

    /**
     * 级别查询统计充值以及提现
     */
    public function getLevelCount(){

        $mobile = input('mobile/s');
        $begin_level = input('begin_level/d');
        $end_level = input('end_level/d');

        if(!$mobile){
            return json(['code'=>0,'msg'=>'请输入手机号码']);
        }
        // if($begin_level){
        //    if(!$level_end){
        //         return json(['code'=>0,'msg'=>'请输入结束等级']);
        //    } 
        // }
        // if($level_end){
        //    if(!$begin_level){
        //         return json(['code'=>0,'msg'=>'请输入开始等级']);
        //    } 
        // }
        // 如果只有手机号则查询当前手机号下的30层
        $user = Db::name('users')->field('id')->where(['mobile'=>$mobile])->find();
        if(!$user){
            return json(['code'=>0,'msg'=>'用户不存在']);
        }
        $dow_uid = '';
        if($begin_level && $end_level){
            // 如果存在等级条件
            $dow_all_line = $this->getDownUids($user['id'],true,1,$end_level);
            if($dow_all_line){
                foreach ($dow_all_line['g_down_ids'] as $v) {
                    $dow_uid .= $v['id'].',';
                }
            }
        }else{
            // 获取当前用户所有下线
            $dow_all_line = $this->getDownUserUid($user['id']);
            if($dow_all_line){
                foreach ($dow_all_line as $v) {
                    $dow_uid .= $v.',';
                }
            }
        }
        $dow_uid = rtrim($dow_uid, ','); // 最终所有1,2,3
        $where['uid'] = ['in',$dow_uid];
        $where['status'] = 1; // 充值完成 
        // 充值总额统计
        $recharge_money = Db::name('recharge')->where($where)->sum('money');

        // 提现总额统计
        $withdraw_money = Db::name('tixian')->where($where)->sum('amount');
        
        $data = [
            'recharge_money' => $recharge_money,
            'withdraw_money' => $withdraw_money
        ];
        return json(['code'=>1,'msg'=>'ok','data'=>$data]);   
    }

    //递归获取用户下线(不包括自己) 以及等级
    public function getDownUids($uid,$need_all=false,$agent_level=1,$agent_level_limit=0){
        $g_down_ids = [];
        if(!$uid){
            return false;
        }
        if($uid||true){
            $member_arr = Db::name('users')->field('id,pid')->where(['pid'=>$uid])->limit(0,5000)->select();
            foreach($member_arr as $mb){
                if($mb['id']&&$mb['id']!=$uid){
                    if($need_all){
                        $mb['agent_level'] = $agent_level;
                        $g_down_ids['g_down_ids'][]=$mb;
                    }else{
                        $g_down_ids['g_down_ids'][]=$mb['id'];
                    }
                    getDownMemberIds2($mb['id'],$need_all,$agent_level+1,$agent_level_limit);
                }   
            }
        }
        return $g_down_ids;
    }


    // 获取当前用户的所有下线(不包括自己)
    public function getDownUserUid($uid){
        $g_down_Uids = '';
        if($uid){
            $member_arr = Db::name('users')->field('id,pid')->where(['pid'=>$uid])->limit(0,5000)->select();
            foreach($member_arr as $mb){
                if($mb['id'] && $mb['id'] != $uid){
                    $g_down_Uids[] = $mb['id'];
                    $this->getDownUserUid($mb['id']);
                }
            }
        }
        return $g_down_Uids;
    }

}