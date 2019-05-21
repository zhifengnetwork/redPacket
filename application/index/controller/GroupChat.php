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
        $red_num = input('red_num/d');
        $red_money = input('red_money/d');
        $ray_point = input('ray_point');
        $password = input('pwd/s');
        $red_money = abs($red_money); // 防止提交负数
        $key = input('key/s');
        if($key != $this->key){
            return message(0,'错误参数-key');
        }
        $user = Db::name('users')->field('id,account,password,salt')->where('id',session('user.id'))->find();
        if(!$user){
            return message(0, '用户不存在');
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

        return message(1, 'ok');

    }

}
