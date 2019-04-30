<?php

# 规则设置
namespace app\admin\controller;

use think\Db;

class Rule extends Common
{

    public function index(){





    }

    # 玩法设置
    public function setting()
    {
        $type = 'red_packets_rebate';
        if($_POST){
            $rebate = isset($_POST['rebate']) ? $_POST['rebate'] : '';
            $desc = isset($_POST['desc']) ? $_POST['desc'] : '';
            
            if($rebate){
                foreach($rebate as $k=>$v){
                    if(Db::name('setting')->where(['name'=>$k,'type'=>$type])->find()){
                        Db::name('setting')->where(['name'=>$k,'type'=>$type])->update(['value'=>$v]);
                    }else{
                        $str = isset($desc[$k]) ? $desc[$k] : $k;
                        Db::name('setting')->insert(['name'=>$k,'value'=>$v,'type'=>$type,'desc'=>$str]);
                    }
                }
            }

            echo "<script>parent.success();</script>";
            exit;
        }

        $info = Db::name('setting')->where('type',$type)->select();
        $list = array();
        if($info){
            foreach($info as $v){
                $list[$v['name']] = $v['value'];
            }
        }

        $this->assign('info', $list);
        return $this->fetch();
    }




}