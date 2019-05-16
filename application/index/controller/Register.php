<?php
namespace app\index\controller;
use think\Db;

class Register extends Base
{
    /**
     * [用户扫码上级二维码注册页面]
     * @return [type] [description]
     */
    public function register()
    {
    	// 接收invite_code到注册页面
    	$invite_code = input('invite_code/s');
        $this->assign('invite_code',$invite_code);
        return $this->fetch();
    }

    /**
     * [用户提交注册处理]
     */
    public function addRegister()
    {
    	if(!isPost()){
            return message(0, '非法提交');
    	}
    	$param = input("post.");
    	$nivite_code = $param['invite_code'];
        $password = trim($param['password']);
    	if(!isMobile($param['mobile'])){
            return message(0, '手机号无效');
    	}
        $pw_len = strlen($password);
        // 判断密码长度6位数字密码
        if($pw_len<6 || $pw_len>6){
            return message(0, '请输入6位数字密码');
        }
        if(!is_numeric($password)){
            return message(0, '密码格式有误');
        }

        // 判断当前手机号是否已注册
    	if(isMobileRegister($param['mobile'])){
            return message(0, '手机号已注册过');
    	}

    	// 判断手机验证码
    	// todo

    	// 判断上级注册码是否存在
    	$pid_invite_code = Db::table('users')->where('invite_code', $nivite_code)->field('id,invite_code')->find();
    	if(!$pid_invite_code){
            return message(0, 'nivite-null');
    	}

    	// 密码处理
    	$salt = create_salt();
    	$password = minishop_md5($password,$salt);
    	
    	$insert_data = [
    		'pid' => $pid_invite_code['id'],
    		'mobile' => $param['mobile'],
    		'password' => $password,
    		'salt' => $salt,
    		'invite_code' => createInviteCode(), // 生成32位邀请码
    		'addtime' => time()
    	];

 	   	// 启动事务
		Db::startTrans();
		try{

			$friend_res = true;
		    $last_id = Db::table('users')->insertGetId($insert_data);

		    // 和上级绑定好友关系 并且上级uid<当前用户uid
            if($pid_invite_code['id'] < $last_id){
                
                $friend_data = [
                    'uid' => $pid_invite_code['id'],  // 上级uid
                    'friend_uid' => $last_id,         // 当前用户uid
                    'create_time' => time()
                ];

                // 判断是否属于好友
                $is_friend = Db::table('chat_friends')->where(['uid'=>$last_id,'friend_uid'=>$pid_invite_code['id']])->find();
                if(!$is_friend){
                    $friend_res = Db::table('chat_friends')->insertGetId($friend_data);
                }
            }
	    	
			// 提交事务
		    Db::commit();
            return message(1, '注册成功', $insert_data);
		} catch (\Exception $e) {
		    // 回滚事务
		    Db::rollback();
            return message(0, '网络异常,稍后再试');
		}
    }


}
