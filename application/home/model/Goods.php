<?php
/**
 * Author: dyr
 * Date: 2016-08-23
 */

namespace app\home\model;

use think\Model;
use think\Db;

/**
 * @package Home\Model
 */
class Goods extends Model
{

    public function getDiscountAttr($value, $data)
    {
        if ($data['market_price'] == 0) {
            $discount = 10;
        } else {
            $discount = round($data['shop_price'] / $data['market_price'], 2) * 10;
        }
        return $discount;
    }
}