<?php
/**
 * Author: dyr
 * Date: 2016-08-23
 */

namespace app\home\model;

use think\model;

/**
 * Class UserAddressModel
 * @package Home\Model
 */
class Pickup extends model
{
    protected $tableName = 'pick_up';
    protected $_validate = array(
        array('pickup_name','require','自提点名称必须！'),
        array('pickup_address','require','自提点地址必须！'),
        array('pickup_phone','require','号码必须！'),
        array('pickup_contact','require','联系人必须！'),
        array('province_id','require','省必须选择！'),
        array('city_id','require','市必须选择！'),
        array('district_id','require','区/镇人必须！'),
    );

    /**
     * 根据省市区获取单个自提点
     * @param $province_id
     * @param $city_id
     * @param $district_id
     * @return mixed
     */
    public function getPickupItemByPCD($province_id,$city_id,$district_id)
    {
        $pickup_where = array('p.province_id' => $province_id, 'p.city_id' => $city_id, 'p.district_id' => $district_id);
        $pickup_list = M('pick_up')
            ->alias('p')
            ->field('p.*,r1.name AS province_name,r2.name AS city_name,r3.name AS district_name')
            ->join('__REGION__ r1','r1.id = p.province_id','LEFT')
            ->join('__REGION__ r2','r2.id = p.city_id','LEFT')
            ->join('__REGION__ r3','r3.id = p.district_id','LEFT')
            ->where($pickup_where)
            ->find();
        return $pickup_list;
    }
    /**
     * 根据省市区获取多个自提点
     * @param $province_id
     * @param $city_id
     * @param $district_id
     * @return mixed
     */
    public function getPickupListByPCD($province_id,$city_id,$district_id)
    {
        $pickup_where = array('p.province_id' => $province_id, 'p.city_id' => $city_id, 'p.district_id' => $district_id);
        $pickup_list = M('pick_up')
            ->alias('p')
            ->field('p.*,r1.name AS province_name,r2.name AS city_name,r3.name AS district_name')
            ->join('__REGION__ r1','r1.id = p.province_id','LEFT')
            ->join('__REGION__ r2','r2.id = p.city_id','LEFT')
            ->join('__REGION__ r3','r3.id = p.district_id','LEFT')
            ->where($pickup_where)
            ->select();
        return $pickup_list;
    }
}