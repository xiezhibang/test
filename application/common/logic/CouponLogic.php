<?php
/**
 * Author: dyr
 * Date: 2016-08-09
 */

namespace app\common\logic;

use app\common\model\Coupon;
use app\common\model\CouponList;
use think\Model;
use think\Db;

/**
 * 回复
 * Class CatsLogic
 * @package common\Logic
 */
class CouponLogic extends Model
{
    /**
     * 获取发放有效的优惠券金额
     * @param type $coupon_id
     * @param type $goods_id
     * @param type $store_id
     * @param type $cat_id
     * @return boolean
     */
    public function getSendValidCouponMoney($coupon_id, $goods_id, $cat_id)
    {
        $curtime = time();
        $coupon = M('coupon')->where('id', $coupon_id)->find();
        $goods_coupon = M('goods_coupon')->where('coupon_id', $coupon_id)->where(function ($query) use ($goods_id, $cat_id) {
            $query->where('goods_id', $goods_id)->whereOr('goods_category_id',$cat_id);
        })->select();
        
        if ($goods_coupon && $coupon 
                && $coupon['send_start_time'] <= $curtime 
                && $coupon['send_end_time'] > $curtime
                && $coupon['createnum'] > $coupon['send_enum']) {
            return $coupon['money'];
        }
        return false;
    }
}