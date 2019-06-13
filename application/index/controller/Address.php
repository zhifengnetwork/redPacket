<?php
namespace app\index\controller;
use think\Db;

// 通讯录
class Address extends Base
{

    protected $user;
    protected $type = 400; // users表客服类型
   	public function _initialize()
    {   
        parent::_initialize();
        if (!is_user_login()) {
            //未登陆跳转到登陆
            $this->redirect('/index/index');
            exit;
        }
        $this->user = session('user');
        $this->assign('user', $this->user);
    }

    /**
     * [通讯录页面]
     * @return json
     */
    public function addressList()
    {

/*        if( !is_complete(session('user.id')) ){

            $info['msg'] = '请完善个人信息';
            $info['url'] = '/index/my/personInfo';
            $this->assign('info',$info);
            return $this->fetch('/message/redirect');
        };
*/
        // 获取当前用户所有好友
        $list = getAllFriends($this->user['id']);
       
        // 将数组按字母A-Z排序
        $list = $this->chartSort($list);
      
        // pre($list);die;
        $this->assign('list', $list);
        return $this->fetch('addressList');
    }
    //判断个人用户信息是否完善
    public function ajax_is(){
        $flag = 0;
        $msg = '';
        if (Request::instance()->isPost()){
            if( !is_complete(session('user.id')) ){
                $msg = '请完善个人信息';
                $flag = 3;

            }
             
        }else{

            $msg = '非法请求';

        }
        
        $string= json_encode(array ('msg'=>$msg,'flag'=>$flag));
        echo $string;

    }



    /**
     * [我的上级页面]
     * @return array
     */
    public function superior(){

        // 判断当前用户是否属于顶级用户,如果是则不进入上级页面
        // $user_is_top = $this->user['pid'];
        // if($this->user['pid']==0){
        //     message(0, '你属于顶级用户');
        // }
        // 获取我的上级
        $mySuperior = Db::name('users')->where('id',$this->user['pid'])->field('id friend_uid,nickname,mobile,head_imgurl,type')->find();
        // if(!$mySuperior){
        //     message(0, '获取上级失败!');
        // }
        $this->assign('list', $mySuperior);
        return $this->fetch('superior');
    }

    /**
     * [人工客服页面]
     * @return array
     */
    public function service(){
        
        // 获取客服列表
        $service_arr = Db::name('users')->where(['type'=>$this->type])->field('id friend_uid,nickname,mobile,head_imgurl,type')->select();
        $this->assign('list', $service_arr);
        return $this->fetch('service');
    }

    /**
     * [充值教程页面]
     * @return array
     */
    public function course(){
        
        // 获取教程图片信息到页面
        // todo
        return $this->fetch('course');
    }

    /**
    * 将数组按字母A-Z排序
    * @return [type] [description]
    */
    protected function chartSort($list){
        
        foreach ($list as $k => &$v) {
            $v['chart'] = $this->getFirstChart( $v['nickname'] );
            $v['uid'] = $this->user['id'];
        }
        $data=[];
        foreach ($list as $ks => $vs) {

            if(empty($vs['head_imgurl'])){
                $vs['head_imgurl'] = "/static/chatWeb/img/00avatar-01-zp.png";
            }else{
                //有数据
                if( @getimagesize(ROOT_PATH.'/public'.$vs['head_imgurl']) == false ){
                    //无头像文件
                    $vs['head_imgurl'] = "/static/chatWeb/img/00avatar-01-zp.png";
                }
        
            }

            if(empty($vs['nickname'])){
                $vs['nickname'] = "默认用户";
            }
           
            // if ( empty( $data[ $vs['chart'] ] ) ) {
            //     $data[ $vs['chart'] ]= [];
            // }else{
                $data[$vs['chart']][] = $vs;
            // }
        }
        
        ksort($data);
        // dump($data);die;
        return $data;
    }

    /**
    * 返回取汉字的第一个字的首字母
    * @param  [type] $str [string]
    * @return [type]      [strind]
    */
    protected function getFirstChart($str){
        // dump($str);
        if( empty($str) ){
            return '';
        }
        $char=ord($str[0]);
        if( $char >= ord('A') && $char <= ord('z') ){
            return strtoupper($str[0]);
        } 
        $s0 = mb_substr($str,0,3); //获取名字的姓
        // dump($s0);
        $s1 = iconv('UTF-8','gb2312//IGNORE',$str);
        $s2 = iconv('gb2312','UTF-8',$s1);
        $s = $s2 == $str?$s1:$str;
        
        // if($str=='嗯'){
        //     echo $s;die;
        // }
        // $s = iconv("utf-8","gb2312//IGNORE",$str);
        // // $s = iconv('UTF-8','gb2312', $s0); //将UTF-8转换成GB2312编码
        // $s = $str;
        // dump($s);
        if (ord($s0)>128) { //汉字开头，汉字没有以U、V开头的
        $asc=ord($s{0})*256+ord($s{1})-65536;
            if($asc>=-20319 and $asc<=-20284)return "A";
            if($asc>=-20283 and $asc<=-19776)return "B";
            if($asc>=-19775 and $asc<=-19219)return "C";
            if($asc>=-19218 and $asc<=-18711)return "D";
            if($asc>=-18710 and $asc<=-18527)return "E";
            if($asc>=-18526 and $asc<=-18240)return "F";
            if($asc>=-18239 and $asc<=-17760)return "G";
            if($asc>=-17759 and $asc<=-17248)return "H";
            if($asc>=-17247 and $asc<=-17418)return "I";
            if($asc>=-17417 and $asc<=-16475)return "J";
            if($asc>=-16474 and $asc<=-16213)return "K";
            if($asc>=-16212 and $asc<=-15641)return "L";
            if($asc>=-15640 and $asc<=-15166)return "M";
            if($asc>=-15165 and $asc<=-14923)return "N";
            if($asc>=-14922 and $asc<=-14915)return "O";
            if($asc>=-14914 and $asc<=-14631)return "P";
            if($asc>=-14630 and $asc<=-14150)return "Q";
            if($asc>=-14149 and $asc<=-14091)return "R";
            if($asc>=-14090 and $asc<=-13319)return "S";
            if($asc>=-13318 and $asc<=-12839)return "T";
            if($asc>=-12838 and $asc<=-12557)return "W";
            if($asc>=-12556 and $asc<=-11848)return "X";
            if($asc>=-11847 and $asc<=-11056)return "Y";
            if($asc>=-11055 and $asc<=-10247)return "Z";
        }else if(ord($s)>=48 and ord($s)<=57)
        { //数字开头
            switch(iconv_substr($s,0,1,'utf-8')){
                case 1:return "Y";
                case 2:return "E";
                case 3:return "S";
                case 4:return "S";
                case 5:return "W";
                case 6:return "L";
                case 7:return "Q";
                case 8:return "B";
                case 9:return "J";
                case 0:return "L";
            }
        }else if(ord($s)>=65 and ord($s)<=90){//大写英文开头
            return substr($s,0,1);
        }else if(ord($s)>=97 and ord($s)<=122){//小写英文开头
            return strtoupper(substr($s,0,1));
        }else{
            return iconv_substr($s0,0,1,'utf-8');
            //中英混合的词语，不适合上面的各种情况，因此直接提取首个字符即可
        }
    }

    public function getFirstChart1($str){
        $s0 = mb_substr($str,0,3); //获取名字的姓
        $s = $str;
        // $s = iconv('UTF-8','gb2312', $s0); //将UTF-8转换成GB2312编码
        // dump($s0);
        if (ord($s0)>128) { //汉字开头，汉字没有以U、V开头的
        $asc=ord($s{0})*256+ord($s{1})-65536;
        if($asc>=-20319 and $asc<=-20284)return "A";
        if($asc>=-20283 and $asc<=-19776)return "B";
        if($asc>=-19775 and $asc<=-19219)return "C";
        if($asc>=-19218 and $asc<=-18711)return "D";
        if($asc>=-18710 and $asc<=-18527)return "E";
        if($asc>=-18526 and $asc<=-18240)return "F";
        if($asc>=-18239 and $asc<=-17760)return "G";
        if($asc>=-17759 and $asc<=-17248)return "H";
        if($asc>=-17247 and $asc<=-17418)return "I";
        if($asc>=-17417 and $asc<=-16475)return "J";
        if($asc>=-16474 and $asc<=-16213)return "K";
        if($asc>=-16212 and $asc<=-15641)return "L";
        if($asc>=-15640 and $asc<=-15166)return "M";
        if($asc>=-15165 and $asc<=-14923)return "N";
        if($asc>=-14922 and $asc<=-14915)return "O";
        if($asc>=-14914 and $asc<=-14631)return "P";
        if($asc>=-14630 and $asc<=-14150)return "Q";
        if($asc>=-14149 and $asc<=-14091)return "R";
        if($asc>=-14090 and $asc<=-13319)return "S";
        if($asc>=-13318 and $asc<=-12839)return "T";
        if($asc>=-12838 and $asc<=-12557)return "W";
        if($asc>=-12556 and $asc<=-11848)return "X";
        if($asc>=-11847 and $asc<=-11056)return "Y";
        if($asc>=-11055 and $asc<=-10247)return "Z";
        }else if(ord($s)>=48 and ord($s)<=57){ //数字开头
            switch(iconv_substr($s,0,1,'utf-8')){
                case 1:return "Y";
                case 2:return "E";
                case 3:return "S";
                case 4:return "S";
                case 5:return "W";
                case 6:return "L";
                case 7:return "Q";
                case 8:return "B";
                case 9:return "J";
                case 0:return "L";
            }
        }else if(ord($s)>=65 and ord($s)<=90){ //大写英文开头
            return substr($s,0,1);
        }else if(ord($s)>=97 and ord($s)<=122){ //小写英文开头
            return strtoupper(substr($s,0,1));
        }
        else
        {
            return iconv_substr($s0,0,1,'utf-8');
            //中英混合的词语，不适合上面的各种情况，因此直接提取首个字符即可
        }
    }



}
