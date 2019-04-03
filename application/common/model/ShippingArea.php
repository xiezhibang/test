<?php
/**
 * Author: IT宇宙人
 * Date: 2015-09-09
 */
namespace app\common\model;
use think\Model;
class ShippingArea extends Model {
    public function plugin(){
        return $this->hasOne('plugin','code','shipping_code');
    }
}
