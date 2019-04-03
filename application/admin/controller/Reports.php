<?php
///**
// * Author: 当燃
// * Date: 2015-12-21
// */
//
//namespace app\admin\controller;
//use app\admin\logic\GoodsLogic;
//use think\Db;
//use think\Page;
//
//class Reports extends Base
//{
//    public $begin;
//    public $end;
//
//    public function _initialize()
//    {
//        parent::_initialize();
//
//        if (I('start_time')) {
//            $begin = I('start_time');
//            $end = I('end_time');
//        } else {
//            $begin = date('Y-m-d', strtotime("-30 day"));//30天前
//            $end = date('Y-m-d', strtotime('+1 days'));
//        }
//        $this->assign('start_time', $begin);
//        $this->assign('end_time', $end);
//        $this->begin = strtotime($begin);
//        $this->end = strtotime($end) + 86399;
//    }
//
//
//    public function jianhuo()
//    {
//
//        $now = strtotime(date('Y-m-d'));
//        $today['today_amount'] = M('order')->where("add_time>$now AND (pay_status=1 or pay_code='weixinH5') and order_status in(1,2,4)")->sum('order_amount');//今日销售总额
//        $today['today_order'] = M('order')->where("add_time>$now and (pay_status=1 or pay_code='weixinH5')")->count();//今日订单数
        //$today['cancel_order'] = M('order')->where("add_time>$now AND order_status=3")->count();//今日取消订单
//
        //$today['jian_huo']=M('order')->where('');
//        if ($today['today_order'] == 0) {
//            $today['sign'] = round(0, 2);
//        } else {
//            $today['sign'] = round($today['today_amount'] / $today['today_order'], 2);
//        }
//        $this->assign('today', $today);
//        $sql = "SELECT COUNT(*) as tnum,sum(order_amount) as amount, FROM_UNIXTIME(add_time,'%Y-%m-%d') as gap from  __PREFIX__order ";
//        $sql .= " where add_time>$this->begin and add_time<$this->end AND (pay_status=1 or pay_code='weixinH5') and order_status in(1,2,4) group by gap ";
//        $res = DB::query($sql);//订单数,交易额
//
//        foreach ($res as $val) {
//            $arr[$val['gap']] = $val['tnum'];
//            $brr[$val['gap']] = $val['amount'];
//            $tnum += $val['tnum'];
//            $tamount += $val['amount'];
//        }
//
//        for ($i = $this->begin; $i <= $this->end; $i = $i + 24 * 3600) {
//            $tmp_num = empty($arr[date('Y-m-d', $i)]) ? 0 : $arr[date('Y-m-d', $i)];
//            $tmp_amount = empty($brr[date('Y-m-d', $i)]) ? 0 : $brr[date('Y-m-d', $i)];
//            $tmp_sign = empty($tmp_num) ? 0 : round($tmp_amount / $tmp_num, 2);
//            $order_arr[] = $tmp_num;
//            $amount_arr[] = $tmp_amount;
//            $sign_arr[] = $tmp_sign;
//            $date = date('Y-m-d', $i);
//            $list[] = array('day' => $date, 'order_num' => $tmp_num, 'amount' => $tmp_amount, 'sign' => $tmp_sign, 'end' => date('Y-m-d', $i + 24 * 60 * 60));
//            $day[] = $date;
//        }
//        rsort($list);
//        $this->assign('list', $list);
//        $result = array('order' => $order_arr, 'amount' => $amount_arr, 'sign' => $sign_arr, 'time' => $day);
//        $this->assign('result', json_encode($result));
//        return $this->fetch();
//    }
//}
//
?>