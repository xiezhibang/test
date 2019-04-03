<?php
/**
 * Author: IT宇宙人
 * Date: 2015-09-09
 *
 * TPshop 公共逻辑类  将放到Application\Common\Logic\   由于很多模块公用 将不在放到某个单独模下面
 */

namespace app\common\logic;

use think\Db;
use think\Model;

//use think\Page;

/**
 * 分销逻辑层
 * Class CatsLogic
 * @package Home\Logic
 */
class DistributLogic //extends Model
{
    public function hello()
    {
        echo 'function hello(){';
    }

    /**
     * 生成分销记录
     */
    public function rebate_log($order)
    {
        $user = M('users')->where("user_id", $order['user_id'])->find();

        $pattern     = tpCache('distribut.pattern'); // 分销模式
        $first_rate  = tpCache('distribut.first_rate'); // 一级比例
        $second_rate = tpCache('distribut.second_rate'); // 二级比例
        $third_rate  = tpCache('distribut.third_switch') ? tpCache('distribut.third_rate') : 0; // 三级比例。加个开关

        $vip_goods_num = 0;

        //按照商品分成 每件商品的佣金拿出来
        if ($pattern == 0) {
            // 获取所有商品分类
            $cat_list    = M('goods_category')->getField('id,parent_id,commission_rate');
            $order_goods = M('order_goods')->master()->where("order_id", $order['order_id'])->select(); // 订单所有商品
            $commission  = $vip_goods_num = $vip_price = 0;
            foreach ($order_goods as $k => $v) {
                $goods = M('goods')->where("goods_id", $v['goods_id'])->find(); // 单个商品的佣金

               /* //如果是福利商品
                if ($goods['is_vip'] ==1) {
                    $vip_goods_num += $v['goods_num']; // 所有商品的累积佣金
                } else {
                    //尖货(美丽升级)产品分销提成
                    $commission += $goods['commission'] * $v['goods_num']; // 所有商品的累积佣金

                    //反美币
                    $vip_price += $goods['vip_price'] * $v['goods_num'];
                }*/

                if ($goods['is_vip'] == 0) {
                    //尖货(美丽升级)产品分销提成
                    $commission += $goods['commission'] * $v['goods_num']; // 所有商品的累积佣金
//                    //反美币
                    $vip_price += $goods['vip_price'] * $v['goods_num'];
                } else {
                    //福利商品  名品
                    $vip_goods_num += $v['goods_num']; // 所有商品的累积佣金
                }
            }
        } else {
            $order_rate = tpCache('distribut.order_rate'); // 订单分成比例
            $commission = $order['goods_price'] * ($order_rate / 100); // 订单的商品总额 乘以 订单分成比例
        }

//        if (!empty($vip_price) && $user['is_vip']) {
//            $data = [
//                'user_id'     => $user['user_id'],
//                'buy_user_id' => $user['user_id'],
//                'nickname'    => $user['nickname'],
//                'order_sn'    => $order['order_sn'],
//                'order_id'    => $order['order_id'],
//                'goods_price' => $order['goods_price'],
//                'money'       => $vip_price,
//                'level'       => 1,
//                'create_time' => time(),
//            ];
//            M('rebate_log')->add($data);
//        }

        // 如果这笔订单没有分销金额
//        if ($commission == 0) return false;

        $first_money  = $commission * ($first_rate / 100); // 一级赚到的钱
        $second_money = $commission * ($second_rate / 100); // 二级赚到的钱
        $thirdmoney   = $commission * ($third_rate / 100); // 三级赚到的钱

//        //  微信消息推送
//        $wx_user = M('wx_user')->find();
//        $jssdk = new \app\mobile\logic\Jssdk($wx_user['appid'], $wx_user['appsecret']);

        $user_distributor = Db::table(
            Db::name('users')
                ->where(['is_vip' => 1, 'user_id' => ['IN', str_replace('u', '', $user['distributor_ids'])], 'distributor_level' => ['IN', [1, 2]]])
                ->order(['distributor_level'=>'ASC','user_id' => 'DESC'])
                ->buildSql()
        )
            ->alias('u')
            ->group('distributor_level')
            ->select();

        if($user['distributor_level']==2){
            unset($user_distributor);
            $user_distributor[0] = $user;

            $third_user_id = Db::name('users')
                ->where([
                    'is_vip' => 1,
                    'user_id' => ['IN', str_replace('u', '', $user['distributor_ids'])],
                    'distributor_level' => 2,
                ])
                ->where(['user_id'=>['neq',$user['user_id']]])
                ->order(['user_id'=>'DESC'])
                ->value('user_id');
        }

        isset($user_distributor[1]) && $third_user_id = Db::name('users')
            ->where([
                'is_vip' => 1,
                'user_id' => ['IN', str_replace('u', '', $user_distributor[1]['distributor_ids'])],
                'distributor_level' => 2,
            ])
            ->where(['user_id'=>['neq',$user_distributor[1]['user_id']]])
            ->order(['user_id'=>'DESC'])
            ->value('user_id');


        if ($vip_goods_num > 0) {
            $data = [
                'buy_user_id' => $user['user_id'],
                'nickname'    => $user['nickname'],
                'order_sn'    => $order['order_sn'],
                'order_id'    => $order['order_id'],
                'goods_price' => $order['goods_price'],
                'level'       => 8,
                'status'      => 8,
                'create_time' => time(),
            ];
            if (isset($user_distributor[0])) {
                $data['user_id'] = $user_distributor[0]['user_id'];
                $data['money']   = tpCache('distribut.first_price') * $vip_goods_num;

                M('rebate_log')->add($data);
            }
            if (isset($user_distributor[1])) {
                $data['user_id'] = $user_distributor[1]['user_id'];
                $data['money']   = tpCache('distribut.second_price') * $vip_goods_num;
                M('rebate_log')->add($data);
            }
            if (isset($third_user_id) && tpCache('distribut.third_switch')) {
                $data['user_id'] = $third_user_id;
                $data['money']   = tpCache('distribut.third_price') * $vip_goods_num;
                M('rebate_log')->add($data);
            }
        }

//        if ($user['distributor_level'] == 1 && isset($user_distributor[0]) && $user_distributor[0]['distributor_level'] == 1) {
//            $user_distributor = [];
//        }

        // 一级 分销商赚 的钱. 小于一分钱的 不存储
        if (isset($user_distributor[0]) && $first_money > 0.01) {
            $data = [
                'user_id'     => $user_distributor[0]['user_id'],
                'buy_user_id' => $user['user_id'],
                'nickname'    => $user['nickname'],
                'order_sn'    => $order['order_sn'],
                'order_id'    => $order['order_id'],
                'goods_price' => $order['goods_price'],
                'money'       => $first_money,
                'level'       => 1,
                'create_time' => time(),
            ];
            M('rebate_log')->add($data);
//            // 微信推送消息
//            $tmp_user = M('users')->where("user_id", $user['first_leader'])->find();
//            if ($tmp_user['oauth'] == 'weixin') {
//                $wx_content = "你的一级下线,刚刚下了一笔订单:{$order['order_sn']} 如果交易成功你将获得 ￥" . $first_money . "奖励 !";
//                $jssdk->push_msg($tmp_user['openid'], $wx_content);
//            }
        }
//
        // 二级 分销商赚 的钱.
        if (isset($user_distributor[1]) && $second_money > 0.01) {
//            if ($user_distributor[0]['distributor_level'] = 1 && $user_distributor[1]['distributor_level'] = 2) {  //判断1级分销为行政,2级分销为白金
            $data = [
                'user_id'     => $user_distributor[1]['user_id'],
                'buy_user_id' => $user['user_id'],
                'nickname'    => $user['nickname'],
                'order_sn'    => $order['order_sn'],
                'order_id'    => $order['order_id'],
                'goods_price' => $order['goods_price'],
                'money'       => $second_money,
                'level'       => 2,
                'create_time' => time(),
            ];
            M('rebate_log')->add($data);
//            }
//            // 微信推送消息
//            $tmp_user = M('users')->where("user_id", $user['second_leader'])->find();
//            if ($tmp_user['oauth'] == 'weixin') {
//                $wx_content = "你的二级下线,刚刚下了一笔订单:{$order['order_sn']} 如果交易成功你将获得 ￥" . $second_money . "奖励 !";
//                $jssdk->push_msg($tmp_user['openid'], $wx_content);
//            }
        }
//
        // 三级 分销商赚 的钱.
        if ($thirdmoney > 0.01 && isset($third_user_id)) {
            $data = [
                'user_id'     => $third_user_id,
                'buy_user_id' => $user['user_id'],
                'nickname'    => $user['nickname'],
                'order_sn'    => $order['order_sn'],
                'order_id'    => $order['order_id'],
                'goods_price' => $order['goods_price'],
                'money'       => $thirdmoney,
                'level'       => 3,
                'create_time' => time(),
            ];
            M('rebate_log')->add($data);
//            // 微信推送消息
//            $tmp_user = M('users')->where("user_id", $user['third_leader'])->find();
//            if ($tmp_user['oauth'] == 'weixin') {
//                $wx_content = "你的三级下线,刚刚下了一笔订单:{$order['order_sn']} 如果交易成功你将获得 ￥" . $thirdmoney . "奖励 !";
//                $jssdk->push_msg($tmp_user['openid'], $wx_content);
//            }
        }

        M('order')->where("order_id", $order['order_id'])->save(["is_distribut" => 1]);  //修改订单为已经分成
        $this->auto_confirm();
    }

    /**
     * 自动分成 符合条件的 分成记录
     */
    public static function auto_confirm()
    {

        $switch = tpCache('distribut.switch');
        if ($switch == 0)
            return false;

        $today_time     = time();
        $distribut_date = tpCache('distribut.date');
        $distribut_time = $distribut_date * (60 * 60 * 24); // 计算天数 时间戳
        $rebate_log_arr = M('rebate_log')->where("status = 2 and ($today_time - confirm) >  $distribut_time")->select();
        foreach ($rebate_log_arr as $key => $val) {
            accountLog($val['user_id'], $val['money'], 0, "订单:{$val['order_sn']}反美币", $val['money']);
            $val['status']       = 3;
            $val['confirm_time'] = $today_time;
            $val['remark']       = $val['remark'] . "满{$distribut_date}天,程序自动分成.";
            M("rebate_log")->where("id", $val['id'])->save($val);
        }
    }
}