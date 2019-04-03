<?php
/**
 * Author: IT宇宙人
 * Date: 2015-09-09
 */
namespace app\common\model;
use think\Model;
class OrderGoods extends Model {

    protected $table='';

    //自定义初始化
    protected function initialize()
    {
        parent::initialize();
    }

    public function goods()
    {
        return $this->hasOne('goods','goods_id','goods_id');
    }
}
