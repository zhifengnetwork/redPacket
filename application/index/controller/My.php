<?php
namespace app\index\controller;
use think\Db;
use think\Session;
use think\Request;
// 我的相关
class My extends Base
{

    private $key;
    public function _initialize()
    {   
        parent::_initialize();
        if (!is_user_login()) {
            //未登陆跳转到登陆
            $this->redirect('/index/index');
            exit;
        }
        $this->key = session('user.key');
    }

    /**
     * [设置页面]
     * @return json
     */
    public function mySet()
    {  
        return $this->fetch('my/set_up');
    }

    /**
     * [账号安全页面]
     * @return [type] [description]
     */
    public function accountAndSecurity()
    {
        $user_arr = Db::name('users')->field('id,nickname,mobile,head_imgurl')->where('id',session('user.id'))->find();
        $this->assign('user_arr', $user_arr);
        return $this->fetch('accountAndSecurity');
    }

    /**
     * [通用页面]
     * @return [type] [description]
     */
    public function commonUse()
    {
        return $this->fetch('commonUse');
    }

    /**
     * [我的钱包]
     * @return array
     */
    public function myWallet(){
        
        // 获取余额
        $user_arr = Db::name('users')->field('id,account')->where('id',session('user.id'))->find();
        $this->assign('user_arr', $user_arr);
        return $this->fetch('my_wallet');
    }

    /**
     * [我的账单]
     * @return array
     */
    public function myBill(){
        $userid = session('user.id');
        // 获取余额
        $user_arr = Db::name('users')->field('id,account')->where('id',session('user.id'))->find();
        //充值记录
        // $rechargeList =  Db::query("select id,uid,time,amount,status from recharge where uid = $userid");
        $rechargeList = Db::name('recharge')->where(['uid'=>$userid])->order('time desc')->select();
        //提现记录
        // $txList =  Db::query("select id,uid,time,amount,status from tixian where uid = $userid");
        $txList = Db::name('tixian')->where(['uid'=>$userid])->order('time desc')->select();
        // $redbagList = Db::query("select id,get_uid,money,get_time,status from chat_red_detail where get_uid = $userid and type =1");
        // 红包记录
        $where['uid'] = $userid;
        $where['type'] = ['in','1,2,3,7,8,9,10,11,12,13'];
        $redbagList = Db::name('chat_red_log')->where($where)->order('create_time desc')->select();
        // var_dump($rechargeList);exit;
        $this->assign('rechargeList', $rechargeList);
        $this->assign('txList', $txList);
        $this->assign('redbagList', $redbagList);
        $this->assign('user_arr', $user_arr);
        return $this->fetch('bill');
    }

    /**
     * [获取用户余额]
     * @return json
     */
    public function getAccount(){
        if(!isPost()){
            return message(0, '非法提交');
        }
        $uid = input('uid/d');
        if(!$uid){
            return message(0, 'no uid');
        }
        $res = Db::name('users')->field('id,account')->where('id', $uid)->find();
        if(!$res){
            return message(0, 'no user');
        }
        return message(1, 'ok',['account'=>$res['account']]);
    }

    /**
     * [我的二维码页面]
     * @return [type] [description]
     */
    public function myQrCode()
    {
        $userid = session('user.id');
        $res = Db::query("select invite_code from users where id = $userid");
        $url = 'http://'.$_SERVER['HTTP_HOST'].'/index/register/register?invite_code='.$res[0]['invite_code'];
        $img =  scerweima($url,$userid);
        // echo $img;exit;
        $this->assign('img', $img);
        
        $logo = 'qq.png';
        if($_SERVER['HTTP_HOST'] == 'www.zxxhrj.cn'){
            $logo = 'saolei.png';
        }

        $this->assign('logo', $logo);


        return $this->fetch('myQrCode');
    }

    /**
     * [我的团队收益页面]
     * @return [type] [description]
     */
    public function myTeamIncome()
    {
        $userid = session('user.id');
        // 我的团队人数 当前用户的所有下线
        $team_list = getDownMemberIds2($userid,true,1,30);
        $team_num = count($team_list);
        unset($GLOBALS['g_down_ids']); // 清空上一次循环全局数据
       
        // 收益详情
        $map['d.uid'] = $userid;
        $map['d.type'] = ['in','4,5,6'];
        $income_info = Db::name('chat_red_log')->alias('d')
                        ->field('d.*,u.nickname')
                        ->join('users u','d.from_id = u.id')
                        ->where($map)
                        ->order('create_time desc')
                        ->select();
        if($team_list && $income_info){
            foreach ($team_list as $v) {
                foreach ($income_info as $k => $vs) {
                    if($v['id'] == $vs['from_id']){
                        $income_info[$k]['level'] = $v['agent_level'];
                    }
                }
            }
        }
        
        // 今日收益
        $where['uid'] = $userid;
        $where['type'] = ['in','4,5,6'];
        $toDay_income = Db::name('chat_red_log')->where($where)->whereTime('create_time', 'today')->sum('money');
        // 月总收益
        $month_income = Db::name('chat_red_log')->where($where)->whereTime('create_time', 'month')->sum('money');

        $this->assign('team_num', $team_num);
        $this->assign('toDay_income', $toDay_income);
        $this->assign('month_income', $month_income);
        $this->assign('income_info', $income_info);
        return $this->fetch('myTeamIncome');
    }

    /**
     * [我的团队页面]
     * @return [type] [description]
     */
    public function myTeam111111()
    {
       
        $userid = session('user.id');
        // 当前用户的30级下线 返回等级
        $team_list = getDownMemberIds2($userid,true,1,30);
        unset($GLOBALS['g_down_ids']); // 清空上一次循环全局数据
        $team_list_in = '';
        $map['id'] = '';
        if($team_list){
            foreach ($team_list as $v) {
                $team_list_in .= $v['id'].',';
            }
            $team_list_in = rtrim($team_list_in, ','); // 最终1,2,3
            $map['id'] = ['in',$team_list_in];
        }
        $list = Db::name('users')->field('id,pid,nickname,head_imgurl')->where($map)->select();

        // 获取下线的上级昵称
        if($list){
            $where['id'] = '';
            $up_list_in = '';
            foreach ($list as $v) {
                $up_list_in .= $v['pid'].',';
            }
            $up_list_in = rtrim($up_list_in, ','); // 最终1,2,3
            $where['id'] = ['in',$up_list_in];
            // 上级列表
            $up_list = Db::name('users')->field('id,pid,nickname')->where($where)->select();
            foreach($team_list  as $ks=>$vs){

                foreach($list as $k=>$v){
                    // 判断当前list里面的数组和获取的下级比对
                    if($v['id']==$vs['id']){
                        $list[$k]['level'] = $vs['agent_level'];  
                    }

                    if($up_list){
                        foreach ($up_list as $vl) {
                            if($v['pid']==$vl['id']){
                                $list[$k]['up_nickname'] = $vl['nickname'];
                            }
                        }
                    }else{
                        $list[$k]['up_nickname'] = '-';
                    }
                }
            }
        }
        // 按照level排序
        $list = array_sort($list,'level','asc');
        $this->assign('list', $list);
        
        return $this->fetch('myTeam');
    }


     /**
     * [我的团队页面]
     * 
     *  优化后
     * 
     * @return [type] [description]
     */
    public function myTeam()
    {
      
        $userid = session('user.id');
       
        
        // 当前用户的30级下线 返回等级
        $team_list = getDownMemberIds2($userid,true,1,30);
      
        $sort = array(
            'direction' => 'SORT_ASC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
            'field'     => 'agent_level',       //排序字段
        );
        $arrSort = array();
        if($team_list){
            foreach($team_list AS $uniqid => $row){
                foreach($row AS $key=>$value){
                    $arrSort[$key][$uniqid] = $value;
                }
            }
            if($sort['direction']){
                array_multisort($arrSort[$sort['field']], constant($sort['direction']), $team_list);
            }
        }
        

     
        // unset($GLOBALS['g_down_ids']); // 清空上一次循环全局数据
        // $team_list_in = '';
        // $map['id'] = '';
        // if($team_list){
        //     foreach ($team_list as $v) {
        //         $team_list_in .= $v['id'].',';
        //     }
        //     $team_list_in = rtrim($team_list_in, ','); // 最终1,2,3
        //     $map['id'] = ['in',$team_list_in];
        // }
        // $list = Db::name('users')->field('id,pid,nickname,head_imgurl')->where($map)->select();

        // // 获取下线的上级昵称
        // if($list){
        //     $where['id'] = '';
        //     $up_list_in = '';
        //     foreach ($list as $v) {
        //         $up_list_in .= $v['pid'].',';
        //     }
        //     $up_list_in = rtrim($up_list_in, ','); // 最终1,2,3
        //     $where['id'] = ['in',$up_list_in];
        //     // 上级列表
        //     $up_list = Db::name('users')->field('id,pid,nickname')->where($where)->select();
        //     foreach($team_list  as $ks=>$vs){

        //         foreach($list as $k=>$v){
        //             // 判断当前list里面的数组和获取的下级比对
        //             if($v['id']==$vs['id']){
        //                 $list[$k]['level'] = $vs['agent_level'];  
        //             }

        //             if($up_list){
        //                 foreach ($up_list as $vl) {
        //                     if($v['pid']==$vl['id']){
        //                         $list[$k]['up_nickname'] = $vl['nickname'];
        //                     }
        //                 }
        //             }else{
        //                 $list[$k]['up_nickname'] = '-';
        //             }
        //         }
        //     }
        // }
        // // 按照level排序
        // $list = array_sort($list,'level','asc');
        $this->assign('list', $team_list);

        // dump(count($list));
        // dump(time());
       

        return $this->fetch('myTeamNew');
    }




    public function activityCenter(){
        return $this->fetch('activity_center');
    }

    /*
    用户个人信息
    */
    public function personInfo(){

        $info = Db::table('users')->where('id',session('user.id'))->field('id,nickname,head_imgurl,mobile')->find();
        // var_dump($info);exit;
        $this->assign('info', $info);
        return $this->fetch('personal_center');

    }

    public function editname(){

        return $this->fetch('edit_name');

    }
    // 个人用户名称
    public function sub_name(){
        $flag=0;
        $msg='';
        $userid = session('user.id');
        if (Request::instance()->isPost()) {
            $nickname = trim(input('post.nickname'));
            if($nickname==''){
                $msg='昵称不能为空！';
            }else{
                $is_eixst = Db::table('users')->where('nickname',$nickname)->field('id')->find();
                if($is_eixst){
                    $msg='昵称已存在！';

                }else{
                    $res = Db::table('users')->where('id',$userid)->update(['nickname' => $nickname]);
                    if( $res ){
                        Session::set('user.nickname',$nickname);
                        $flag = 1;
                        $msg = '成功！';
                    }else{

          
                        $msg = '失败！';
                    }

                }
            }

        }else{
            $msg='非法请求';


        }   

        $string= json_encode(array ('msg'=>$msg,'flag'=>$flag));
        echo $string;
    }
    //个人用户图像修改
    public function sub_photo(){

        $flag=0;
        $msg='';
        $userid = session('user.id');
        if (Request::instance()->isPost()) {
   
            $base64_img = input('post.photo');
        
            if($base64_img==''){
                $msg='图像不能为空！';
            }else{
                $head_imgurl =  uploadImg($base64_img); 
                if($head_imgurl =='上传失败'){
                        $msg = '失败！';  
                }else{            

                    $res = Db::table('users')->where('id',$userid)->update(['head_imgurl' => $head_imgurl]);

                    if( $res ){
                        Session::set('user.head_imgurl',$head_imgurl);
                        $flag = 1;
                        $msg = '成功！';
                    }else{

                        $msg = '失败！';
                    }
                }

            }

        }else{
            $msg='非法请求';


        }   

        $string= json_encode(array ('msg'=>$msg,'flag'=>$flag));
        echo $string;   

    }


    public function add_friend(){

        return $this->fetch();

    }
    // 添加朋友时 搜索
    public function search_friend(){

        $flag = 0;// 参数错误
        $msg = '';
        $userid = session('user.id');
        if (Request::instance()->isPost()) {
            $mobile = trim(input('post.mobile'));
            if(!isMobile($mobile)){

                $msg = '请输入正确的手机号！';
            }else{

                $res = Db::query("select mobile,id,nickname,head_imgurl from users where mobile = {$mobile}");
                if(!$res){
                    $msg = '用户不存在';
                }elseif($userid == $res[0]['id']){

                    $msg = '不能添加自己为好友';

                }else{

                    // 判断是否为好友
                    $friends_id = $res[0]['id'];
                    $sql = "select id from chat_friends where (uid = {$friends_id} and friend_uid ={$userid}) or (friend_uid = {$friends_id} and uid ={$userid}) ";
                    // echo $sql;exit;
                    //判断是否为好友
                    $isFriends = Db::query($sql);
                    if($isFriends){

                        $flag = 2;
                        $msg = $res[0];

                    }else{ //不是好友关系
                        $flag = 1; 
                        $msg = $res[0];
                    }
                }
            }    
        }else{

            $msg = '非法请求';

        }
        
        $string= json_encode(array ('msg'=>$msg,'flag'=>$flag));
        echo $string;
    }

    // 添加好友申请提交
    public function sub_friend(){

        $flag=0;
        $msg='';
        $userid = session('user.id');
        if (Request::instance()->isPost()) {
   
            $friends_id = input('post.id');
        
            if($friends_id==''){
                $msg='参数错误！';
            }elseif($friends_id ==$userid){
                $msg='不能添加自己为好友';
            }else{
                $res = Db::query("select id from users where id = $friends_id");
                if(!$res){
                   $msg='用户不存在'; 
                }else{
                    // 判断是否为好友
                    $sql = "select id from chat_friends where (uid = {$friends_id} and friend_uid ={$userid}) or (friend_uid = {$friends_id} and uid ={$userid}) ";
                    // echo $sql;exit;
                    //判断是否为好友
                    $isFriends = Db::query($sql);
                    if($isFriends){
                        $msg = "已经是好友";

                    }else{ //不是好友关系
                       $friend_data = [
                        'uid' => $userid,  // 用户id
                        'friend_uid' => $friends_id,    // 好友id      
                        'create_time' => time(),
                        'status' => 3
                        ];
                        $friend_res = Db::table('chat_friends_ask')->insertGetId($friend_data);
                        if($friend_res>0){
                            $flag = 1;
                            $msg = '已发送';
                        }else{
                            $msg = '发送失败';

                        }
                    }
                }
            }

        }else{
            $msg='非法请求';


        }   

        $string= json_encode(array ('msg'=>$msg,'flag'=>$flag));
        echo $string;
    }


    public function update_loginpw(){
       
        return $this->fetch();
    }

    //修改登录密码
    public function reset_loginpw(){
        $flag = 0;
        $msg = '';
        $userid =  session('user.id');
        if (Request::instance()->isPost()){
            $oldpwd = trim(input('post.oldpwd'));
            $newpwd = trim(input('post.newpwd'));
            if($newpwd =='' ){
                $msg = '请输入新密码';
            }elseif($oldpwd ==''){

                $msg = '请输入旧密码';

            }else{

                //更新数据
                $info = Db::table('users')->where('id',$userid)->field('salt,password')->find();
                $old_pwd = minishop_md5($oldpwd,$info['salt']);

                $new_pwd =  minishop_md5($newpwd,$info['salt']);

                if($old_pwd != $info['password']){
                    $msg ='原密码输入错误';

                }else{
                    $res = Db::table('users')->where('id',$userid)->update(['password' => $new_pwd]);
                    if($res){

                        $flag = 1;
                        $msg = '登录密码重置成功';   
                    }else{
                        $msg = '登录密码重置失败';
                    }

                }

            
            }

        }else{

            $msg = '非法请求';


        }
        $string= json_encode(array ('msg'=>$msg,'flag'=>$flag));
        echo $string;

    }

    //好友申请列表
    public function friends(){
        $userid = session('user.id');
        $info = Db::query("select u.nickname,u.head_imgurl,f.uid,f.status,f.id from chat_friends_ask as f left join users as u on u.id = f.uid where f.friend_uid = $userid and status =3");
        $this->assign('info', $info);

        return $this->fetch('new_friend');


    }

    //好友申请审核
    public function sub_ckfriends(){

        $flag=0;
        $msg='';
        $userid = session('user.id');
        
        if (Request::instance()->isPost()) {
   
            $id = input('post.id');
            $type = input('post.type');

            if($id==''){
                $msg='参数错误！';
        
            }else{
                $info = Db::query("select uid,friend_uid from chat_friends_ask where id = $id and status=3");
                
                Db::table('chat_friends_ask')->where('id', $id)->update(['status' =>$type]);
                
                $friend_data = [
                    'uid' => $info[0]['uid'],  // 用户id
                    'friend_uid' => $info[0]['friend_uid'],    // 好友id      
                    'create_time' => time()
   
                ];
                if($type ==1){
                    $friend_res = Db::table('chat_friends')->insertGetId($friend_data);
                    if($friend_res>0){
                        $flag = 1;
                        $msg = '已同意';
                    }else{
                        $msg = '操作失败';

                    }    
                }else{
                    $flag = 1;
                    $msg = '已拒绝';


                }     
            }

        }else{
            $msg='非法请求';


        }   

        $string= json_encode(array ('msg'=>$msg,'flag'=>$flag));
        echo $string;

    }




}
