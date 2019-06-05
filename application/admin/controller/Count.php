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
        // 发包返水type=4
        $total_send_red_backwater = Db::name('chat_red_log')->where('type',4)->sum('money');
        // 抢包返利type=5
        $total_get_red_rebate = Db::name('chat_red_log')->where('type',5)->sum('money');
        // 抢包返水type=6
        $total_get_red_backwater = Db::name('chat_red_log')->where('type',6)->sum('money');
        // 抢包奖励type=7
        $total_get_red_award = Db::name('chat_red_log')->where('type',7)->sum('money');
        // 发包奖励type=8
        $total_send_red_award = Db::name('chat_red_log')->where('type',8)->sum('money');
        // 系统返利type=9
        $total_system_rebate = Db::name('chat_red_log')->where('type',9)->sum('money');
        // 中雷type=10
        $total_ray = Db::name('chat_red_log')->where('type',10)->sum('money');

        $this->assign('send_red_total_money', $send_red_total_money);
        $this->assign('today_send_red_money', $today_send_red_money);
        $this->assign('month_send_red_money', $month_send_red_money);

        $this->assign('out_time_money', $out_time_money);
        $this->assign('today_out_time_money', $today_out_time_money);
        $this->assign('month_out_time_money', $month_out_time_money);

        $this->assign('get_red_total_money', $get_red_total_money);
        $this->assign('today_get_red_total_money', $today_get_red_total_money);

        $this->assign('total_send_red_rebate', $total_send_red_rebate);
        $this->assign('total_send_red_backwater', $total_send_red_backwater);
        $this->assign('total_get_red_rebate', $total_get_red_rebate);
        $this->assign('total_get_red_backwater', $total_get_red_backwater);
        $this->assign('total_get_red_award', $total_get_red_award);
        $this->assign('total_send_red_award', $total_send_red_award);
        $this->assign('total_system_rebate', $total_system_rebate);
        $this->assign('total_ray', abs($total_ray));

        return $this->fetch('users/count');
    }

}