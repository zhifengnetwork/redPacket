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
    		return json(['code'=>0, 'msg'=>'非法提交', 'data'=>""]);
    	}
    	$param = input("post.");
    	$nivite_code = $param['invite_code'];
        $password = trim($param['password']);
    	if(!isMobile($param['mobile'])){
    		return json(['code'=>0, 'msg'=>'手机号无效', 'data'=>""]);
    	}
        $pw_len = strlen($password);
        // 判断密码长度6位数字密码
        if($pw_len<6 || $pw_len>6){
            return json(['code'=>0, 'msg'=>'请输入6位数字密码', 'data'=>""]);
        }
        if(is_numeric($password)){
            return json(['code'=>0, 'msg'=>'密码格式有误', 'data'=>""]);
        }


        // 判断当前手机号是否已注册
    	if(isMobileRegister($param['mobile'])){
    		return json(['code'=>0, 'msg'=>'手机号已注册过', 'data'=>""]);
    	}

    	// 判断手机验证码
    	// todo

    	// 判断上级注册码是否存在
    	$pid_invite_code = Db::table('users')->where('invite_code', $nivite_code)->field('id,invite_code')->find();
    	if(!$pid_invite_code){
    		return json(['code'=>0, 'msg'=>'nivite-null', 'data'=>""]);
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

		    // 和上级绑定好友关系
	    	$friend_data = [
	    		'uid' => $last_id,
	    		'friend_uid' => $pid_invite_code['id']
	 	   	];
	 	   	// 判断是否属于好友
	 	   	$is_friend = Db::table('chat_friends')->where(['uid'=>$last_id,'friend_uid'=>$pid_invite_code['id']])->find();
	 	   	if(!$is_friend){
				$friend_res = Db::table('chat_friends')->insertGetId($friend_data);
	 	   	}

			// 提交事务
		    Db::commit();
    		return json(['code'=>1, 'msg'=>'注册成功', 'data'=>$insert_data]);

		} catch (\Exception $e) {
		    // 回滚事务
		    Db::rollback();
		    return json(['code'=>0, 'msg'=>'网络异常,稍后再试', 'data'=>""]);
		}
    }


}
