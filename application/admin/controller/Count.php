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
        // 超时退回总额
        $out_time_money = Db::name('chat_red_detail')->where('type',2)->sum('money');
        // 今天超时退回总额
        $today_out_time_money = Db::name('chat_red_detail')->where('type',2)->whereTime('out_time', 'today')->sum('money');

        // 抢包总金额
        $get_red_total_money = Db::name('chat_red_detail')->where('type',1)->sum('money');
        // 今天抢包总金额
        $today_get_red_total_money = Db::name('chat_red_detail')->where('type',1)->whereTime('get_time', 'today')->sum('money');

        $this->assign('send_red_total_money', $send_red_total_money);
        $this->assign('today_send_red_money', $today_send_red_money);
        $this->assign('out_time_money', $out_time_money);
        $this->assign('today_out_time_money', $today_out_time_money);

        $this->assign('get_red_total_money', $get_red_total_money);
        $this->assign('today_get_red_total_money', $today_get_red_total_money);

        return $this->fetch('users/count');
    }

}