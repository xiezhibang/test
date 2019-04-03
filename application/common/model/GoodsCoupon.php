<?php
/**
 * Author: IT宇宙人
 * Date: 2015-09-09
 */
namespace app\common\model;

use think\Db;
use think\Model;

class GoodsCoupon extends Model
{
    public function goods()
    {
        return $this->hasOne('Goods','goods_id','goods_id');
    }
    public function goodsCategory()
    {
        return $this->hasOne('GoodsCategory','id','goods_category_id');
    }
}
