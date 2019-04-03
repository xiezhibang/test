<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/12
 * Time: 10:08
 */

namespace app\mobile\controller;
use think\Db;

class NewGoods extends Member {

    public function goodsList(){
        //        $this->isVip();
        $this->assign('is_vip',$this->isVip());
        return $this->fetch('newhtml/min');
    }


}