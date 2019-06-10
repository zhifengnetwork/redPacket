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

    // 规则海报
    public function haibao(){
        $query = "select id,title,url,create_time from poster where title ='海报'";
        $info = Db::query($query);

        $this->assign('list', $info[0]);
        return $this->fetch();


    }
    // 更新海报
    public function edit_haibao(){

        $file = request()->file('img');
   
        $path = ROOT_PATH . 'public' . DS . 'uploads';

        if($file){
            $info = $file->move($path);
            $path =  $info->getSaveName();
     
            Db::table('poster')->where('title', '海报')->update(['url' => $path]);

            

        }

        $this->success('更新成功', 'rule/haibao');

    }




}