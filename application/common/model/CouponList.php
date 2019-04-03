<?php
/**
 * Author: IT宇宙人
 * Date: 2015-09-09
 */
namespace app\common\model;

use think\Db;
use think\Model;
use app\common\logic\FlashSaleLogic;
use app\common\logic\GroupBuyLogic;

class CouponList extends Model
{
    public function coupon()
    {
        return $this->hasOne('coupon','id','cid');
    }
}
