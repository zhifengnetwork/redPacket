<?php
namespace app\index\controller;
use think\Db;
class Find extends Base
{

    // 发现页面
    public function index()
    {    

    	
    	$info = Db::table('poster')->where('title','海报')->field('url')->find();

    	$this->assign('img', $info);


        return $this->fetch('find');
    }

}
