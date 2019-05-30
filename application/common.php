<?php
use think\Db;
use think\Request;


function array_sort($arr, $keys, $type = 'desc'){  
    $key_value = $new_array = array();  
    foreach ($arr as $k => $v) {  
        $key_value[$k] = $v[$keys];  
    }  
    if ($type == 'asc') {  
        asort($key_value);  
    } else {  
        arsort($key_value);  
    }  
    reset($key_value);  
    foreach ($key_value as $k => $v) {  
        $new_array[$k] = $arr[$k];  
    }  
    return $new_array;  
}

// 递归获取用户所有上线(不包括自己)
function getUpMemberIds($uid){
	global $g_up_mids,$i;
	if($uid){
        $up_i = 0;
        if($up_i!=30){
            $member = Db::name('users')->field('id,pid')->where(['id'=>$uid])->find();
            if($member&&$member['pid']!=$uid){
                if($member['pid']){
                    $g_up_mids[]=$member['pid'];
                    getUpMemberIds($member['pid']);
                    $up_i++;
                }
            }
        }
    }
	return $g_up_mids;
}

// 获取当前用户的所有下线(不包括自己)
function getDownUserUids2($uid){
    global $g_down_Uids;
	if($uid){
        $member_arr = Db::name('users')->field('id,pid')->where(['pid'=>$uid])->limit(0,3000)->select();
		foreach($member_arr as $mb){
			if($mb['id'] && $mb['id'] != $uid){
               
                $g_down_Uids[] = $mb['id'];
                getDownUserUids2($mb['id']);
            }
		}
    }
	return $g_down_Uids;
}

//递归获取用户下线(不包括自己) 以及等级
function getDownMemberIds2($uid,$need_all=false,$agent_level=1,$agent_level_limit=0){
    global $g_down_ids;
    if($agent_level_limit&&$agent_level>$agent_level_limit){
        return false;
    }
    if($uid||true){
        $member_arr = Db::name('users')->field('id,pid')->where(['pid'=>$uid])->limit(0,3000)->select();
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
    return $g_down_ids['g_down_ids'];
}


//数组转换成[配置项名称]获取数据
function arr2name($data,$key=''){
    $return_data=array();
    if(!$data||!is_array($data)){
        return $return_data;
    }
    if(!$key){
        $key='name';
    }
    foreach($data as $dv){
        $return_data[$dv[$key]]=$dv;
    }
    return $return_data;
}

/**
 * @param $total  [要发的红包总额]
 * @param int $num  [红包个数]
 * @return array [生成红包金额]
 */
function createRedDate($total, $num)
{
    if(!$total || !$num){return false;}
    $min = 0.01; // 保证最小金额
    $wamp = array();
    $returnData = array();
    for ($i = 1; $i < $num; ++$i) {
        $safe_total = ($total - ($num - $i) * $min) / ($num - $i); // 随机安全上限 红包金额的最大值
        if ($safe_total < 0) break;
        $money = @mt_rand($min * 100, $safe_total * 100) / 100; // 随机产生一个红包金额
        $total = $total - $money;   // 剩余红包总额
        $wamp[$i] = sprintf("%.2f",$money); // 保留两位有效数字
    }
    $wamp[$i] = sprintf("%.2f",$total);
    $returnData['redMoneyList'] = $wamp;
    $returnData['newTotalMoney'] = array_sum($wamp);
    return $returnData;
}

/**
 *  生成订单号
 * @return string
 */
function createOrderNo(){
    return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
}

/**
 * [获取用户所有好友]
 * @param  int $uid [当前登录用户uid]
 * @return array
 */
function getAllFriends($uid){

    if(!$uid){return false;}
    //使用UNION ALL，因为不存在重复的(UNION会判重)
    $friends_arr = Db::query("SELECT friend_uid AS friends FROM chat_friends WHERE uid = $uid UNION ALL SELECT uid AS friends FROM chat_friends WHERE friend_uid = $uid");
    $temp_array = [];
    foreach ($friends_arr as $val) {
        //把数组元素组合为一个字符串
        $val = join(",",$val);
        $temp_array[] = $val;
    }
    // pre($temp_array);die;
    $friends = implode(",", $temp_array);
    // 获取好友信息 friend_uid
    $friends_info = Db::name('users')->field('id friend_uid,nickname,mobile,head_imgurl,type,is_lock')->where('id', 'in', $friends)->select();
    return $friends_info;
}

/**
 * 返回客户端信息
 * @return json
 */
function message($code, $msg, $data=""){
    return json(['code'=>$code, 'msg'=>$msg, 'data'=>$data]);
}

/**
 * 判断前台用户是否登录
 * @return boolean
 */
function is_user_login()
{
    $sessionUser = session('user');
    return !empty($sessionUser);
}

// 判断是post请求
function isPost(){
    return Request::instance()->isPost();
}

// 判断当前手机号是否已注册
function isMobileRegister($mobile){

    if(!$mobile){return false;}
    return Db::table('users')->where('mobile',$mobile)->find();
}

// 生成32位唯一邀请码
function createInviteCode(){
    return md5(time() . mt_rand(1,1000000));
}

// 获取用户信息
function getUserInfo($mobile,$type=2){

    if(!$mobile){return false;}
    if($type==2){
        $user = Db::table('users')->where('mobile',$mobile)
            ->field('id,pid,nickname,account,head_imgurl,mobile,invite_code,type,is_lock')
            ->find();
    }else{
        $user = Db::table('users')->where('mobile',$mobile)
            ->field('id,pid,nickname,account,head_imgurl,mobile,password,salt,invite_code,type,is_lock')
            ->find();
    }
    return $user;
    
}



function pre($data){
    header("Content-type: text/html; charset=utf-8");
    echo '<pre>';
    print_r($data);
}

function pred($data){
    echo '<pre>';
    print_r($data);die;
}

/**
 * 创建盐
 * @author tangtanglove <dai_hang_love@126.com>
 */
function create_salt($length = -6)
{
    return $salt = substr(uniqid(rand()), $length);
}

/**
 * minishop md5加密方法
 * @author tangtanglove <dai_hang_love@126.com>
 */
function minishop_md5($string, $salt)
{
    return md5(md5($string) . $salt);
}

/**
 * 获取菜单列表
 */
function get_menu_list()
{
    static $menu_tree;
    if (!$menu_tree) {
        $menuList  = Db::table('menu')->order('sort ASC')->where('status', 1)->select();
        $menu_tree = list_to_tree($menuList);
    }
    return $menu_tree;
}

/**
 * 获取活动类型下拉列表
 */
function get_menu_list_html($selid = -1, $def_tit = '无')
{
    $arr = get_menu_list();

    $list = '<option value="0">' . $def_tit . '</option>';
    foreach ($arr as $val) {
        $list .= '<option value="' . $val['id'] . '"' . ($val['id'] == $selid ? ' selected' : '') . '>' . $val['title'] . '</option>';
        if (!isset($val['_child']) || !$val['_child']) {
            continue;
        }
        foreach ($val['_child'] as $v) {
            $list .= '<option value="' . $v['id'] . '"' . ($v['id'] == $selid ? ' selected' : '') . '>' . '------' . $v['title'] . '</option>';
        }
    }
    return $list;
}

/**
 * 时间戳格式化
 * @param int $time
 * @return string 完整的时间显示
 */
function time_format($time = null, $format = 'Y-m-d H:i:s')
{
    $time = $time === null ? time() : intval($time);
    return date($format, $time);
}

/**
 * 把返回的数据集转换成Tree
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
 * @return array
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0)
{
    // 创建Tree
    $tree = array();
    if (is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] = &$list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId = $data[$pid];
            if ($root == $parentId) {
                $tree[] = &$list[$key];
            } else {
                if (isset($refer[$parentId])) {
                    $parent           = &$refer[$parentId];
                    $parent[$child][] = &$list[$key];
                }
            }
        }
    }
    return $tree;
}

/**
 * 验证手机号是否正确
 */
function isMobile($mobile)
{
    if (!is_numeric($mobile)) {
        return false;
    }
    return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? true : false;
}

function curl_post_query($url, $data)
{
    //初始化
    $ch = curl_init(); //初始化一个CURL对象

    // curl_setopt($ch, CURLOPT_URL, $url);
    // curl_setopt($ch, CURLOPT_FAILONERROR, false);
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    // curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    // curl_setopt($ch, CURLOPT_HTTPHEADER, array('content-type: application/x-www-form-urlencoded;charset=UTF-8'));
    // curl_setopt($ch, CURLOPT_POSTFIELDS, $data);


    curl_setopt($ch1, CURLOPT_URL, $url);
    //设置你所需要抓取的URL
    curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
    //设置curl参数，要求结果是否输出到屏幕上，为true的时候是不返回到网页中
    curl_setopt($ch1, CURLOPT_POST, 1);
    curl_setopt($ch1, CURLOPT_HEADER, false);
    //post提交
    curl_setopt($ch1, CURLOPT_POSTFIELDS, http_build_query($data));
    $data = curl_exec($ch);
    // var_dump(curl_error($ch));
    //运行curl,请求网页。
    curl_close($ch);
    
    // var_dump($data);
    // exit;
    //显示获得的数据
    return $data;
}

/**
 * 秒转时分秒
 */
function changeTimeType($seconds)
{
    if ($seconds > 3600) {
        $hours   = intval($seconds / 3600);
        $minutes = $seconds % 3600;
        $time    = $hours . "时" . gmstrftime('%M', $minutes) . "分" . gmstrftime('%S', $minutes) . "秒";
    } else {
        $time = gmstrftime('%M', $seconds) . "分" . gmstrftime('%S', $seconds) . "秒";
    }
    return $time;
}

/**
 * 日期处理函数 （如：2017-8-15 改成2天前）
 * @param unknown $the_time
 * @return unknown|string
 */
function dayfast($the_time)
{
    $now_time = date("Y-m-d H:i:s", time());
    $now_time = strtotime($now_time);
    $dur      = $now_time - $the_time;
    if ($dur < 0) {
        return $the_time;
    } else {
        if ($dur < 60) {
            return $dur . '秒前';
        } else {
            if ($dur < 3600) {
                return floor($dur / 60) . '分钟前';
            } else {
                if ($dur < 86400) {
                    return floor($dur / 3600) . '小时前';
                } else {
                    if ($dur < 259200) {
//3天内
                        return floor($dur / 86400) . '天前';
                    } else {
                        $the_time = date("Y-m-d", $the_time);
                        return $the_time;
                    }
                }
            }
        }
    }
}

function get_real_ip()
{
    $ip = false;
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(', ', $_SERVER['HTTP_X_FORWARDED_FOR']);
        if ($ip) {
            array_unshift($ips, $ip);
            $ip = false;}
        for ($i = 0; $i < count($ips); $i++) {
            if (!preg_match('/^(10│172.16│192.168)./i', $ips[$i])) {
                $ip = $ips[$i];
                break;
            }
        }
    }
    return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
}

/**
 * 系统加密方法
 * @param string $data 要加密的字符串
 * @param string $key  加密密钥
 * @param int $expire  过期时间 单位 秒
 * @return string
 *
 */
function think_encrypt($data, $key = '', $expire = 0)
{
    $key  = md5(empty($key) ? config('api_auth_key') : $key);
    $data = base64_encode($data);
    $x    = 0;
    $len  = strlen($data);
    $l    = strlen($key);
    $char = '';

    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) {
            $x = 0;
        }

        $char .= substr($key, $x, 1);
        $x++;
    }

    $str = sprintf('%010d', $expire ? $expire + time() : 0);

    for ($i = 0; $i < $len; $i++) {
        $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1))) % 256);
    }
    return str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($str));
}

/**
 * 系统解密方法
 * @param  string $data 要解密的字符串 （必须是think_encrypt方法加密的字符串）
 * @param  string $key  加密密钥
 * @return string
 */

function think_decrypt($data, $key = '')
{
    $key  = md5(empty($key) ? config('api_auth_key') : $key);
    $data = str_replace(array('-', '_'), array('+', '/'), $data);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
        $data .= substr('====', $mod4);
    }
    $data   = base64_decode($data);
    $expire = substr($data, 0, 10);
    $data   = substr($data, 10);

    /*if($expire > 0 && $expire < time()) {
    return '';
    }*/
    $x    = 0;
    $len  = strlen($data);
    $l    = strlen($key);
    $char = $str = '';

    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) {
            $x = 0;
        }

        $char .= substr($key, $x, 1);
        $x++;
    }

    for ($i = 0; $i < $len; $i++) {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        } else {
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return base64_decode($str);
}

/**
 * 写入日志文件
 */
function write_log($filename, $data = '')
{
    $str = 'time:' . date('Y-m-d H:i:s', time()) . ' ' . microtime() . PHP_EOL;
    if ($data) {
        $str .= var_export($data, true);
    } else {
        foreach (input() as $key => $value) {
            $str .= ' ' . $key . ' => ' . $value . ',';
        }
    }
    $str .= PHP_EOL . str_repeat("-", 100) . PHP_EOL;
    file_put_contents($filename, $str, FILE_APPEND);
}

/**
 * 文件保存名（用房间唯一id和当前局数拼接）
 */
function get_file_name($id, $rounds, $bankerWinCount)
{
    $savename = 'data' . DS . substr(md5($id . 'HIJABC' . $rounds), 0, 8) . DS . $id . '_' . $rounds . $bankerWinCount . '.txt';
    return $savename;
}

function get_period_time($type = 'day', $now = 0, $fmt = 0)
{
    $rs = false;
    !$now && ($now = time());
    switch ($type) {
        case 'all':
            $begin_time = 0;
            $end_time   = INT . MAX;
            break;
        case 'yst':
            $rs['beginTime'] = date('Y-m-d 00:00:00', strtotime('-1 days'));
            $rs['endTime']   = date('Y-m-d 23:59:59', strtotime('-1 days'));
            break;
        case 'day': //今天
            $rs['beginTime'] = date('Y-m-d 00:00:00', $now);
            $rs['endTime']   = date('Y-m-d 23:59:59', $now);
            break;
        case 'week': //本周
            $time            = '1' == date('w') ? strtotime('Monday', $now) : strtotime('last Monday', $now);
            $rs['beginTime'] = date('Y-m-d 00:00:00', $time);
            $rs['endTime']   = date('Y-m-d 23:59:59', strtotime('Sunday', $now));
            break;
        case 'ssmonth': //上月至月末
            $rs['beginTime'] = date('Y-m-01 00:00:00', strtotime('-2 month'));
            $rs['endTime']   = date('Y-m-' . intval(date('d')) . ' 23:59:59', strtotime('-2 month'));
            break;
        case 'smonth': //上月至今
            $rs['beginTime'] = date('Y-m-01 00:00:00', strtotime('-1 month'));
            $rs['endTime']   = date('Y-m-' . intval(date('d')) . ' 23:59:59', strtotime('-1 month'));
            break;
        case 'day7': //最近7天
            $rs['beginTime'] = date('Y-m-d', strtotime('-7 day'));
            $rs['endTime']   = date('Y-m-d') - 1;
            break;
        case 'day15': //最近15天
            $rs['beginTime'] = date('Y-m-d', strtotime('-15 day'));
            $rs['endTime']   = date('Y-m-d') - 1;
            break;
        case 'day30': //最近15天
            $rs['beginTime'] = date('Y-m-d', strtotime('-30 day'));
            $rs['endTime']   = date('Y-m-d') - 1;
            break;
        case 'day90': //最近15天
            $rs['beginTime'] = date('Y-m-d', strtotime('-90 day'));
            $rs['endTime']   = date('Y-m-d') - 1;
            break;
        case 'month': //本月
            $rs['beginTime'] = date('Y-m-d 00:00:00', mktime(0, 0, 0, date('m', $now), '1', date('Y', $now)));
            $rs['endTime']   = date('Y-m-d 23:39:59', mktime(0, 0, 0, date('m', $now), date('t', $now), date('Y', $now)));
            break;
        case '3month': //三个月
            $time            = strtotime('-2 month', $now);
            $rs['beginTime'] = date('Y-m-d 00:00:00', mktime(0, 0, 0, date('m', $time), 1, date('Y', $time)));
            $rs['endTime']   = date('Y-m-d 23:39:59', mktime(0, 0, 0, date('m', $now), date('t', $now), date('Y', $now)));
            break;
        case 'half_year': //半年内
            $time            = strtotime('-5 month', $now);
            $rs['beginTime'] = date('Y-m-d 00:00:00', mktime(0, 0, 0, date('m', $time), 1, date('Y', $time)));
            $rs['endTime']   = date('Y-m-d 23:39:59', mktime(0, 0, 0, date('m', $now), date('t', $now), date('Y', $now)));
            break;
        case 'year': //今年内
            $rs['beginTime'] = date('Y-m-d 00:00:00', mktime(0, 0, 0, 1, 1, date('Y', $now)));
            $rs['endTime']   = date('Y-m-d 23:39:59', mktime(0, 0, 0, 12, 31, date('Y', $now)));
            break;
        case 'b': //比较上月***
            $rs['beginTime'] = date("2017-10-10", time());
            $rs['endTime']   = date('Y-m-' . intval(date('d')) . ' 23:59:59', strtotime('-1 month'));
            break;
        case 's': //比较上2个月***
            $rs['beginTime'] = date("2017-10-10", time());
            $rs['endTime']   = date('Y-m-' . intval(date('d')) . ' 23:59:59', strtotime('-2 month'));
        case 'ss': //比较上3个月***
            $rs['beginTime'] = date("2017-10-10", time());
            $rs['endTime']   = date('Y-m-' . intval(date('d')) . ' 23:59:59', strtotime('-3 month'));
            break;

    }
    if ($rs && $fmt == 0) {
        $rs['beginTime'] = strtotime($rs['beginTime']);
        $rs['endTime']   = strtotime($rs['endTime']);
    }
    return $rs;
}
function get_month_text_html($id)
{
    if ($id == -1) {
        $id = date('m') - 1;
    }
    $time = date('Y');
    $data = [$time . '-1', $time . '-2', $time . '-3', $time . '-4', $time . '-5', $time . '-6', $time . '-7', $time . '-8', $time . '-9', $time . '-10', $time . '-11', $time . '-12'];
    $list = '<option value="-1">请选择月份</option>';
    foreach ($data as $key => $val) {
        $list .= '<option value="' . $val . '"' . ($key == $id ? ' selected' : '') . '> ' . $val . '</option>';
    }
    return $list;
}

//获取跳转游戏房间详情url
function redirect_game_info_url($gid, $rel_id)
{

    $arr = [
        '10' => 'gold_sss_room/info',
        '20' => 'gold_bairenniu_round/info',
        '21' => 'gold_tongbiniu_round/info',
        '22' => 'gold_qzn_round/info',
        '30' => 'gold_dezhou_round/info',
        '40' => 'gold_bairenlonghu_round/info',
        '50' => 'gold_bairenhh_round/info',
        '60' => 'gold_bcbm_round/info',
        '70' => 'gold_shz_round/info',
    ];
    $url = '';
    if ($arr[$gid]) {
        $url = url($arr[$gid]) . '?id=' . $rel_id;
    }
    return $url;
}

function api_public_key()
{
    return 'gWE5zJjazR3FQgaYtSWUxWhOxmo';
}
/** 导出excel文件
 * file_name 文件名称
 * title 第一行的标题 [A,B]
 * data 封装的数组,对应title的位置[['A','B'],[]]
 * */
function excel_export($file_name,$title,$data){
	if(count($title)<1||count($data)<1){
		return false;
	}
	
	vendor('PHPExcel.PHPExcel');
	$objPHPExcel = new \PHPExcel();
	$sheet=$objPHPExcel->setActiveSheetIndex(0);
	
	$colunm_num=count($title);
	$letter=range('A', 'Z');
	$letter=array_slice($letter, 0,$colunm_num);
	
	for($j=0;$j<$colunm_num;$j++){
		$sheet->setCellValue($letter[$j].'1',$title[$j]);
	}
	
	$i=2;
		
	foreach ((array)$data as $val){
	
		//设置为文本格式
		for($j=0;$j<count($letter);$j++){
			$sheet->setCellValue($letter[$j].$i,$val[$j]);
			
			$objPHPExcel->getActiveSheet()->getStyle($letter[$j].$i)->getNumberFormat()
			->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
		}
	
		++$i;
	}
		
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename="'.$file_name.'.xls"');
	header('Cache-Control: max-age=0');
	$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save('php://output');
	exit;
	
}

//二维数组排序
function towArraySort ($data,$key,$order = SORT_ASC) {

    try{
        //        dump($data);
        $last_names = array_column($data,$key);
        array_multisort($last_names,$order,$data);
//        dump($data);
        return $data;
    }catch (\Exception $e){
        return false;
    }

}

/**
 * 判断某个值是否存在二维数组中(判断是否属于好友用到)
 * 
 */
function deep_in_array($value, $array) {
    foreach($array as $item) {
        if(!is_array($item)) {
            if ($item == $value) {
                return $item;
            } else {
                continue;
            }
        }

        if(in_array($value, $item)) {
            return $item;
        } else if(deep_in_array($value, $item)) {
            return $item;
        }
    }
    return false;
 }

 function base_img($base,$names,$images='',$info=''){
    $saveName = request()->time().rand(0,99999) . '.png';

    $img=base64_decode($base);
    //生成文件夹
    // $names = "distribution_set" ;
    $name = "{$names}/" .date('Ymd',time()) ;
    if (!file_exists(ROOT_PATH .Config('c_pub.img').$names)){ 
        mkdir(ROOT_PATH .Config('c_pub.img').$names,0777,true);
    } 
    //保存图片到本地
    file_put_contents(ROOT_PATH .Config('c_pub.img').$name.$saveName,$img);

    if( $info ){
        @unlink( ROOT_PATH .Config('c_pub.img') . $info );
    }
    return $name.$saveName;
}

function uploadImg($base64){ 
    header("content-type:text/html;charset=utf-8"); 
    $base64_image = str_replace(' ', '+', $base64); 
    //post的数据里面，加号会被替换为空格，需要重新替换回来，如果不是post的数据，则注释掉这一行 
    if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image, $result)){ 
        //匹配成功 
        if($result[2] == 'jpeg'){ 
            $image_name = uniqid().'.jpg'; 
           //纯粹是看jpeg不爽才替换的 
        }else{ 
            $image_name = uniqid().'.'.$result[2]; 
        } 
        $image_file = "./uploads/".date('Ymd',time()).'/'; 
        if (!file_exists($image_file)) { 
                mkdir($image_file,0755,true); 
        } 
        $image_url = "./uploads/".date('Ymd',time()).'/'."{$image_name}"; 
        $res_url = "/uploads/".date('Ymd',time()).'/'."{$image_name}"; 
        //服务器文件存储路径 
        if (file_put_contents($image_url, base64_decode(str_replace($result[1], '', $base64_image)))){ 
            return $res_url; 
        }else{ 
            return '上传失败'; 
        } 
    }else{ 
        return '上传失败'; 
    }    
        
} 
//推广码专用
function scerweima($url='',$id=''){
    Vendor('phpqrcode.phpqrcode');
    $value = $url;         //二维码内容
    $errorCorrectionLevel = 'L';  //容错级别
    $matrixPointSize = 5;      //生成图片大小
    //生成二维码图片
    $filename = "uploads/ewm/".$id.'.png';
    if(!file_exists($filename)){

        QRcode::png($value,$filename , $errorCorrectionLevel, $matrixPointSize, 2);
        $QR = $filename;        //已经生成的原始二维码图片文件
        $QR = imagecreatefromstring(file_get_contents($QR));
        //输出图片
        imagepng($QR, 'qrcode.png');
        imagedestroy($QR);
        
    }
    
    return $filename;
    
}

/*
判断用户昵称和图像是否设置
$userid 用户id
*/
function is_complete($userid){

    $info = Db::table('users')->where('id',$userid)->field('id,nickname,head_imgurl')->find();
    if($info['nickname'] =='' or $info['head_imgurl']==''){
        return false;
    }
    return true;

}

/**
 * 调用layer弹出错误提示
 */
function layer_error($msg, $re = true, $url = ''){
    header("Content-type: text/html; charset=utf-8"); 

    echo "<h1 style='margin-top:30%; text-align:center;color:red;'>$msg</h1>";
    if($re){
        if($url){
            echo "<script>setTimeout(function(){window.location.href='$url';},3000);</script>";
        }else{
            echo "<script>setTimeout(function(){window.history.go(-1);},3000);</script>";
        }
        
    }
    exit;
}