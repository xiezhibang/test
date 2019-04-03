<?php
/**
 * Author: 当燃      
 * Date: 2015-12-21
 */

namespace app\admin\controller;
use app\admin\logic\GoodsLogic;
use think\Db;
use think\Page;

class Report extends Base
{
    public $begin;
    public $end;

    public function _initialize()
    {
        parent::_initialize();

        if (I('start_time')) {
            $begin = I('start_time');
            $end = I('end_time');
        } else {
            $begin = date('Y-m-d', strtotime("-30 day"));//30天前
            $end = date('Y-m-d', strtotime('0 days'));
        }
        $this->assign('start_time', $begin);
        $this->assign('end_time', $end);
        $this->begin = strtotime($begin);
        $this->end = strtotime($end) + 86399;
    }

    public function index()
    {
        $now = strtotime(date('Y-m-d'));
        $today['today_amount'] = M('order')->where("add_time>$now AND (pay_status=1 or pay_code='cod') and order_status in(1,2,4)")->sum('order_amount');//今日销售总额
        $today['today_order'] = M('order')->where("add_time>$now and (pay_status=1 or pay_code='cod')")->count();//今日订单数
        $today['cancel_order'] = M('order')->where("add_time>$now AND order_status=3")->count();//今日取消订单

        //$today['jian_huo']=M('order')->where('');
        if ($today['today_order'] == 0) {
            $today['sign'] = round(0, 2);
        } else {
            $today['sign'] = round($today['today_amount'] / $today['today_order'], 2);
        }
        $this->assign('today', $today);
        $sql = "SELECT COUNT(*) as tnum,sum(order_amount) as amount, FROM_UNIXTIME(add_time,'%Y-%m-%d') as gap from  __PREFIX__order ";
        $sql .= " where add_time>$this->begin and add_time<$this->end AND (pay_status=1 or pay_code='cod') and order_status in(1,2,4) group by gap ";
        $res = DB::query($sql);//订单数,交易额

        foreach ($res as $val) {
            $arr[$val['gap']] = $val['tnum'];
            $brr[$val['gap']] = $val['amount'];
            $tnum += $val['tnum'];
            $tamount += $val['amount'];
        }

        for ($i = $this->begin; $i <= $this->end; $i = $i + 24 * 3600) {
            $tmp_num = empty($arr[date('Y-m-d', $i)]) ? 0 : $arr[date('Y-m-d', $i)];
            $tmp_amount = empty($brr[date('Y-m-d', $i)]) ? 0 : $brr[date('Y-m-d', $i)];
            $tmp_sign = empty($tmp_num) ? 0 : round($tmp_amount / $tmp_num, 2);
            $order_arr[] = $tmp_num;
            $amount_arr[] = $tmp_amount;
            $sign_arr[] = $tmp_sign;
            $date = date('Y-m-d', $i);
            $list[] = array('day' => $date, 'order_num' => $tmp_num, 'amount' => $tmp_amount, 'sign' => $tmp_sign, 'end' => date('Y-m-d', $i + 24 * 60 * 60));
            $day[] = $date;
        }
        rsort($list);
        $this->assign('list', $list);
        $result = array('order' => $order_arr, 'amount' => $amount_arr, 'sign' => $sign_arr, 'time' => $day);
        $this->assign('result', json_encode($result));
        return $this->fetch();
    }

    public function saleTop()
    {
        $sql = "select goods_name,goods_sn,sum(goods_num) as sale_num,sum(goods_num*goods_price) as sale_amount from __PREFIX__order_goods ";
        $sql .= " where is_send = 1 group by goods_id order by sale_amount DESC limit 100";
        $res = DB::cache(true, 3600)->query($sql);
        $this->assign('list', $res);
        return $this->fetch();
    }

    public function userTop()
    {
//		$p = I('p',1);
//		$start = ($p-1)*20;
        $mobile = I('mobile');
        $email = I('email');
        if ($mobile) {
            $where = "and b.mobile='$mobile'";
        }
        if ($email) {
            $where = "and b.email='$email'";
        }
        $sql = "select count(a.order_id) as order_num,sum(a.order_amount) as amount,a.user_id,b.mobile,b.email,b.nickname from __PREFIX__order as a left join __PREFIX__users as b ";
        $sql .= " on a.user_id = b.user_id where a.add_time>$this->begin and a.add_time<$this->end and a.pay_status=1 $where group by user_id order by amount DESC limit 0,100";
        $res = DB::cache(true)->query($sql);
        $this->assign('list', $res);
//		if(empty($where)){
//			$count = M('order')->where("add_time>$this->begin and add_time<$this->end and pay_status=1")->group('user_id')->count();
//			$Page = new Page($count,20);
//			$show = $Page->show();
//			$this->assign('page',$show);
//		}
        return $this->fetch();
    }

    public function saleList()
    {
        $cat_id = I('cat_id', 0);
        $brand_id = I('brand_id', 0);
        $where = "where b.add_time>$this->begin and b.add_time<$this->end ";
        if ($cat_id > 0) {
            $where .= " and g.cat_id=$cat_id";
            $this->assign('cat_id', $cat_id);
        }
        if ($brand_id > 0) {
            $where .= " and g.brand_id=$brand_id";
            $this->assign('brand_id', $brand_id);
        }

        $sql2 = "select count(*) as tnum from __PREFIX__order_goods as a left join __PREFIX__order as b on a.order_id=b.order_id ";
        $sql2 .= " left join __PREFIX__goods as g on a.goods_id = g.goods_id $where";
        $total = DB::query($sql2);
        $count = $total[0]['tnum'];
        $Page = new Page($count, 20);
        $show = $Page->show();

        $sql = "select a.*,b.order_sn,b.shipping_name,b.pay_name,b.add_time from __PREFIX__order_goods as a left join __PREFIX__order as b on a.order_id=b.order_id ";
        $sql .= " left join __PREFIX__goods as g on a.goods_id = g.goods_id $where ";
        $sql .= "  order by add_time desc limit {$Page->firstRow},{$Page->listRows}";
        $res = DB::query($sql);
        $this->assign('list', $res);
        $this->assign('page', $show);

        $GoodsLogic = new GoodsLogic();
        $brandList = $GoodsLogic->getSortBrands();
        $categoryList = $GoodsLogic->getSortCategory();
        $this->assign('categoryList', $categoryList);
        $this->assign('brandList', $brandList);
        return $this->fetch();
    }

    public function user()
    {
        $today = strtotime(date('Y-m-d'));
        $month = strtotime(date('Y-m-01'));
        $user['today'] = D('users')->where("reg_time>$today")->count();//今日新增会员
        $user['month'] = D('users')->where("reg_time>$month")->count();//本月新增会员
        $user['total'] = D('users')->count();//会员总数
        $user['user_money'] = D('users')->sum('user_money');//会员余额总额
        $res = M('order')->cache(true)->distinct(true)->field('user_id')->select();
        $user['hasorder'] = count($res);
        $this->assign('user', $user);
        $sql = "SELECT COUNT(*) as num,FROM_UNIXTIME(reg_time,'%Y-%m-%d') as gap from __PREFIX__users where reg_time>$this->begin and reg_time<$this->end group by gap";
        $new = DB::query($sql);//新增会员趋势
        foreach ($new as $val) {
            $arr[$val['gap']] = $val['num'];
        }

        for ($i = $this->begin; $i <= $this->end; $i = $i + 24 * 3600) {
            $brr[] = empty($arr[date('Y-m-d', $i)]) ? 0 : $arr[date('Y-m-d', $i)];
            $day[] = date('Y-m-d', $i);
        }
        $result = array('data' => $brr, 'time' => $day);
        $this->assign('result', json_encode($result));
        return $this->fetch();
    }

    //尖货销售
    public function jianhuo()
    {

        $now = strtotime(date('Y-m-d'));
        //var_dump(date('Y-m-d,H-i-s',$now));exit;
        $today['today_amount'] = M('order')->alias('o')
            ->join('order_goods og','o.order_id=og.order_id')
            ->join('goods g','og.goods_id=g.goods_id')
            ->where("add_time>$now AND pay_status=1 and pay_code='meibi' and order_status in(1,2,4) and g.is_vip=0")
            ->sum('order_amount');//今日销售总额
        $today['today_order'] = M('order')->alias('o')
            ->join('order_goods og','o.order_id=og.order_id')
            ->join('goods g','og.goods_id=g.goods_id')
            ->where("add_time>$now and pay_status=1 and pay_code='meibi' and g.is_vip=0")
            ->count();//今日订单数

        //var_dump($today);exit;
        if ($today['today_order'] == 0) {
            $today['sign'] = round(0, 2);
        } else {
            $today['sign'] = round($today['today_amount'] / $today['today_order'], 2);
        }
        $this->assign('today', $today);

        $sql = "SELECT COUNT(o.order_id) as tnum,sum(o.order_amount) as amount, FROM_UNIXTIME(o.add_time,'%Y-%m-%d') as gap from  __PREFIX__order as o left join __PREFIX__order_goods as og on o.order_id=og.order_id left join __PREFIX__goods as g on og.goods_id=g.goods_id";
        $sql .= " where o.add_time>$this->begin and o.add_time<$this->end AND o.pay_status=1 and o.pay_code='meibi' and o.order_status in(1,2,4) and g.is_vip=0 group by gap ";
        $res = DB::query($sql);//订单数,交易额

        //var_dump($res);exit;



        //统计尖货美币销售额
        $resultss=M('order')->alias('o')
            ->join('order_goods og','o.order_id=og.order_id')
            ->join('goods g','og.goods_id=g.goods_id')
            ->where("add_time>$this->begin and add_time<$this->end and pay_code='meibi' and pay_status=1 and order_status in(1,2,4) and g.is_vip=0")->sum('order_amount');//尖货美币销售总额
        //var_dump($resultss);exit;
        $this->assign('resultss',$resultss);
        foreach ($res as $val) {
            $arr[$val['gap']] = $val['tnum'];
            $brr[$val['gap']] = $val['amount'];
            $tnum += $val['tnum'];
            $tamount += $val['amount'];
        }
        //echo "<pre>";
        //var_dump($res);
        //var_dump($arr);exit;

        for ($i = $this->begin; $i <= $this->end; $i = $i + 24 * 3600) {
            $tmp_num = empty($arr[date('Y-m-d', $i)]) ? 0 : $arr[date('Y-m-d', $i)];
            $tmp_amount = empty($brr[date('Y-m-d', $i)]) ? 0 : $brr[date('Y-m-d', $i)];
            $tmp_sign = empty($tmp_num) ? 0 : round($tmp_amount / $tmp_num, 2);
            $order_arr[] = $tmp_num;
            $amount_arr[] = $tmp_amount;
            $sign_arr[] = $tmp_sign;
            $date = date('Y-m-d', $i);
            $list[] = array('day' => $date, 'order_num' => $tmp_num, 'amount' => $tmp_amount, 'sign' => $tmp_sign, 'end' => date('Y-m-d', $i + 24 * 60 * 60));
            $day[] = $date;
        }
        rsort($list);
        $this->assign('list', $list);
        $result = array('order' => $order_arr, 'amount' => $amount_arr, 'sign' => $sign_arr, 'time' => $day);
        $this->assign('result', json_encode($result));
        return $this->fetch();

    }
    //VIP统计
    public function viptotal()
    {

        $user['total'] = M('users')->where("reg_time>$this->begin and reg_time<$this->end and is_vip=1")->count('user_id');//会员总数

        $user['amount']= M('order')->alias('o')
                       ->join('order_goods og','o.order_id=og.order_id')
                       ->join('goods g','og.goods_id=g.goods_id')
                       ->where("add_time>$this->begin and add_time<$this->end and pay_status=1 and order_status in(1,2,4) and g.is_vip=-1")->sum('order_amount');//VIP注册金额
        //var_dump($user);exit;
        $res = M('order')->cache(true)->distinct(true)->field('user_id')->select();
        $user['hasorder'] = count($res);
        $this->assign('user', $user);
        $sql = "SELECT COUNT(*) as num,FROM_UNIXTIME(reg_time,'%Y-%m-%d') as gap from __PREFIX__users where reg_time>$this->begin and reg_time<$this->end and is_vip=1 group by gap";
        $new = DB::query($sql);//新增VIP趋势
        foreach ($new as $val) {
            $arr[$val['gap']] = $val['num'];
        }

        for ($i = $this->begin; $i <= $this->end; $i = $i + 24 * 3600) {
            $brr[] = empty($arr[date('Y-m-d', $i)]) ? 0 : $arr[date('Y-m-d', $i)];
            $day[] = date('Y-m-d', $i);
        }
        $result = array('data' => $brr, 'time' => $day);
        $this->assign('result', json_encode($result));
        //return $this->fetch();
        return $this->fetch();
    }
    //运费概览
    public function transport()
    {

        $user['meibiamount']= M('order')->alias('o')
            ->join('order_goods og','o.order_id=og.order_id')
            ->join('goods g','og.goods_id=g.goods_id')
            ->where("add_time>$this->begin and add_time<$this->end and pay_status=1 and pay_code='meibi' and order_status in(1,2,4) and g.is_vip=1")->sum('order_amount');//任领美币支付金额

        $user['codbiamount']= M('order')->alias('o')
            ->join('order_goods og','o.order_id=og.order_id')
            ->join('goods g','og.goods_id=g.goods_id')
            ->where("add_time>$this->begin and add_time<$this->end and pay_status=1 and pay_code='cod' and order_status in(1,2,4) and g.is_vip=1")->sum('order_amount');//任领现金支付金额

        $user['meibiamount1']= M('order')->alias('o')
            ->join('order_goods og','o.order_id=og.order_id')
            ->join('goods g','og.goods_id=g.goods_id')
            ->where("add_time>$this->begin and add_time<$this->end and pay_status=1 and pay_code='meibi' and order_status in(1,2,4) and g.is_vip=2")->sum('order_amount');//名品美币支付金额

        $user['codamount']= M('order')->alias('o')
            ->join('order_goods og','o.order_id=og.order_id')
            ->join('goods g','og.goods_id=g.goods_id')
            ->where("add_time>$this->begin and add_time<$this->end and pay_status=1 and pay_code='cod' and order_status in(1,2,4) and g.is_vip=2")->sum('order_amount');//名品美币支付金额
        $this->assign('user',$user);

        $this->assign('result',json_encode($user));
        if ($today['today_order'] == 0) {
            $today['sign'] = round(0, 2);
        } else {
            $today['sign'] = round($today['today_amount'] / $today['today_order'], 2);
        }
        $this->assign('today', $today);
        //任领总额
        $sql = "SELECT COUNT(*) as tnum,sum(order_amount) as amount, FROM_UNIXTIME(add_time,'%Y-%m-%d') as gap from  __PREFIX__order as o left join __PREFIX__order_goods as og on o.order_id=og.order_id left join __PREFIX__goods as g on og.goods_id=g.goods_id";
        $sql .= " where add_time>$this->begin and add_time<$this->end AND pay_status=1 and order_status in(1,2,4) and g.is_vip=1 group by gap ";
        $res = DB::query($sql);//订单数,交易额

        foreach ($res as $val) {
            $arr[$val['gap']] = $val['tnum'];
            $brr[$val['gap']] = $val['amount'];
            $tnum += $val['tnum'];
            $tamount += $val['amount'];
        }

        for ($i = $this->begin; $i <= $this->end; $i = $i + 24 * 3600) {
            $tmp_num = empty($arr[date('Y-m-d', $i)]) ? 0 : $arr[date('Y-m-d', $i)];
            $tmp_amount = empty($brr[date('Y-m-d', $i)]) ? 0 : $brr[date('Y-m-d', $i)];
            $tmp_sign = empty($tmp_num) ? 0 : round($tmp_amount / $tmp_num, 2);
            $order_arr[] = $tmp_num;
            $amount_arr[] = $tmp_amount;
            $sign_arr[] = $tmp_sign;
            $date = date('Y-m-d', $i);
            $list[] = array('day' => $date,'amount' => $tmp_amount,'end' => date('Y-m-d', $i + 24 * 60 * 60));
            $day[] = $date;
        }
        //名品总额
        $sql1 = "SELECT COUNT(*) as tnum,sum(order_amount) as amount, FROM_UNIXTIME(add_time,'%Y-%m-%d') as gap from  __PREFIX__order as o left join __PREFIX__order_goods as og on o.order_id=og.order_id left join __PREFIX__goods as g on og.goods_id=g.goods_id";
        $sql1 .= " where add_time>$this->begin and add_time<$this->end AND pay_status=1 and order_status in(1,2,4) and g.is_vip=2 group by gap ";
        $res1 = DB::query($sql1);//订单数,交易额

        //var_dump($res1);exit;

        foreach ($res1 as $val) {
            $arr1[$val['gap']] = $val['tnum'];
            $brr1[$val['gap']] = $val['amount'];
            $tnum1 += $val['tnum'];
            $tamount1 += $val['amount'];
        }

        for ($j = $this->begin; $j <= $this->end; $j = $j + 24 * 3600) {
            $tmp_num1 = empty($arr1[date('Y-m-d', $j)]) ? 0 : $arr1[date('Y-m-d', $j)];
            $tmp_amount1 = empty($brr1[date('Y-m-d', $j)]) ? 0 : $brr1[date('Y-m-d', $j)];
            $tmp_sign1 = empty($tmp_num1) ? 0 : round($tmp_amount1 / $tmp_num1, 2);
            $order_arr1[] = $tmp_num1;
            $amount_arr1[] = $tmp_amount1;
            $sign_arr1[] = $tmp_sign1;
            $date = date('Y-m-d', $j);
            $list1[] = array('day' => $date1,'amount1' => $tmp_amount1,'end' => date('Y-m-d', $j + 24 * 60 * 60));
            $day[] = $date;
        }
        //rsort($list);
        rsort($list);
        $this->assign('list', $list);
        $this->assign('list1',$list1);
        $result = array('order' => $order_arr, 'amount' => $amount_arr,'time' => $day,'order1'=>$order_arr,'amount1'=>$amount_arr1);
        //echo "<pre>";
        //var_dump($result);exit;
        $this->assign('result', json_encode($result));
        return $this->fetch();
    }
	
	//财务统计
	public function finance(){
		$sql = "SELECT sum(b.goods_num*b.member_goods_price) as goods_amount,sum(a.shipping_price) as shipping_amount,sum(b.goods_num*b.cost_price) as cost_price,";
		$sql .= "sum(a.coupon_price) as coupon_amount,FROM_UNIXTIME(a.add_time,'%Y-%m-%d') as gap from  __PREFIX__order a left join __PREFIX__order_goods b on a.order_id=b.order_id ";
		$sql .= " where a.add_time>$this->begin and a.add_time<$this->end AND a.pay_status=1 and a.shipping_status=1 and b.is_send=1 group by gap order by a.add_time";
		$res = DB::cache(true)->query($sql);//物流费,交易额,成本价
		
		foreach ($res as $val){
			$arr[$val['gap']] = $val['goods_amount'];
			$brr[$val['gap']] = $val['cost_price'];
			$crr[$val['gap']] = $val['shipping_amount'];
			$drr[$val['gap']] = $val['coupon_amount'];
		}
			
		for($i=$this->begin;$i<=$this->end;$i=$i+24*3600){
			$date = $day[] = date('Y-m-d',$i);
			$tmp_goods_amount = empty($arr[$date]) ? 0 : $arr[$date];
			$tmp_cost_amount = empty($brr[$date]) ? 0 : $brr[$date];
			$tmp_shipping_amount = empty($crr[$date]) ? 0 : $crr[$date];
			$tmp_coupon_amount = empty($drr[$date]) ? 0 : $drr[$date];
			
			$goods_arr[] = $tmp_goods_amount;
			$cost_arr[] = $tmp_cost_amount;
			$shipping_arr[] = $tmp_shipping_amount;
			$coupon_arr[] = $tmp_coupon_amount;
			$list[] = array('day'=>$date,'goods_amount'=>$tmp_goods_amount,'cost_amount'=>$tmp_cost_amount,
					'shipping_amount'=>$tmp_shipping_amount,'coupon_amount'=>$tmp_coupon_amount,'end'=>date('Y-m-d',$i+24*60*60));
		}
                rsort($list);
		$this->assign('list',$list);
		$result = array('goods_arr'=>$goods_arr,'cost_arr'=>$cost_arr,'shipping_arr'=>$shipping_arr,'coupon_arr'=>$coupon_arr,'time'=>$day);
		$this->assign('result',json_encode($result));
		return $this->fetch();
	}
	
}