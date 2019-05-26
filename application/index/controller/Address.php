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
        // 获取当前用户所有好友
        $list = getAllFriends($this->user['id']);
        // 将数组按字母A-Z排序
        $list = $this->chartSort($list);
        // pre($list);die;
        $this->assign('list', $list);
        return $this->fetch('addressList');
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
            if ( empty( $data[ $vs['chart'] ] ) ) {
                $data[ $vs['chart'] ]= [];
            }
            $data[ $vs['chart'] ][] = $vs;
        }
        ksort($data);
        return $data;
    }

    /**
    * 返回取汉字的第一个字的首字母
    * @param  [type] $str [string]
    * @return [type]      [strind]
    */
    protected function getFirstChart($str){
        if( empty($str) ){
            return '';
        }
        $char=ord($str[0]);
        if( $char >= ord('A') && $char <= ord('z') ){
            return strtoupper($str[0]);
        } 
        $s1 = iconv('UTF-8','gb2312',$str);
        $s2 = iconv('gb2312','UTF-8',$s1);
        $s = $s2 == $str?$s1:$str;
        $asc=ord($s{0})*256+ord($s{1})-65536;
            if($asc>=-20319&&$asc<=-20284) return 'A';
            if($asc>=-20283&&$asc<=-19776) return 'B';
            if($asc>=-19775&&$asc<=-19219) return 'C';
            if($asc>=-19218&&$asc<=-18711) return 'D';
            if($asc>=-18710&&$asc<=-18527) return 'E';
            if($asc>=-18526&&$asc<=-18240) return 'F';
            if($asc>=-18239&&$asc<=-17923) return 'G';
            if($asc>=-17922&&$asc<=-17418) return 'H';
            if($asc>=-17417&&$asc<=-16475) return 'J';
            if($asc>=-16474&&$asc<=-16213) return 'K';
            if($asc>=-16212&&$asc<=-15641) return 'L';
            if($asc>=-15640&&$asc<=-15166) return 'M';
            if($asc>=-15165&&$asc<=-14923) return 'N';
            if($asc>=-14922&&$asc<=-14915) return 'O';
            if($asc>=-14914&&$asc<=-14631) return 'P';
            if($asc>=-14630&&$asc<=-14150) return 'Q';
            if($asc>=-14149&&$asc<=-14091) return 'R';
            if($asc>=-14090&&$asc<=-13319) return 'S';
            if($asc>=-13318&&$asc<=-12839) return 'T';
            if($asc>=-12838&&$asc<=-12557) return 'W';
            if($asc>=-12556&&$asc<=-11848) return 'X';
            if($asc>=-11847&&$asc<=-11056) return 'Y';
            if($asc>=-11055&&$asc<=-10247) return 'Z';
            return null;
    }



}
