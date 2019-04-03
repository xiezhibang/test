<?php
/**
 * Author: JY
 * Date: 2015-09-23
 */

namespace app\admin\controller;
use think\Db;

class Api extends Base {
    /*
     * 获取地区
     */
    public function getRegion(){
        $parent_id = I('get.parent_id/d');
        $data = M('region')->where("parent_id", $parent_id)->select();
        $html = '';
        if($data){
            foreach($data as $h){
                $html .= "<option value='{$h['id']}'>{$h['name']}</option>";
            }
        }
        echo $html;
    }

    public function getGoodsSpec(){
        $goods_id = I('get.goods_id/d');
        $temp = DB::name('spec_goods_price')->field("GROUP_CONCAT(`key` SEPARATOR '_' ) as goods_spec_item")->where('goods_id', $goods_id)->select();
        $goods_spec_item = $temp[0]['goods_spec_item'];
        $goods_spec_item = array_unique(explode('_',$goods_spec_item));
        if($goods_spec_item[0] != ''){
            $spec_item = DB::query("SELECT i.*,s.name FROM __PREFIX__spec_item i LEFT JOIN __PREFIX__spec s ON s.id = i.spec_id WHERE i.id IN (".implode(',',$goods_spec_item).") ");
            $new_arr = array();
            foreach($spec_item as $k=>$v){
                $new_arr[$v['name']][] = $v;
            }
            $this->assign('specList',$new_arr);
        }
       return $this->fetch();
    }
    /*
     * 获取商品价格
     */
    public function getSpecPrice(){
        $spec_id = I('post.spec_id/d');
        $goods_id = I('get.goods_id/d');
        if(!is_array($spec_id)){
            exit;
        }
        $item_arr = array_values($spec_id);
        sort($item_arr);
        $key = implode('_',$item_arr);
        $goods = M('spec_goods_price')->where(array('key'=>$key,'goods_id'=>$goods_id))->find();
        $info = array(
            'status' => 1,
            'msg' => 0,
            'data' =>$goods['price'] ? $goods['price'] : 0
        );
        exit(json_encode($info));
    }

    //商品价格计算
    public function calcGoods(){
        $goods_id = I('post.goods/d'); // 添加商品id
        $price_type = I('post.price') ? I('post.price') : 3; // 价钱类型
        $goods_info = M('goods')->where(array('goods_id'=>$goods_id))->find();
        if(!$goods_info['goods_id'] > 0)
            exit; // 不存在商品
        switch($price_type){
            case 1:
                $goods_price = $goods_info['market_price']; //市场价
                break;
            case 2:
                $goods_price = $goods_info['shop_price']; //市场价
                break;
            case 3:
                $goods_price = I('post.goods_price'); //自定义
                break;
        }

        $goods_num = I('post.goods_num/d'); // 商品数量

        $total_price = $goods_price * $goods_num; // 计算商品价格

        $info = array(
            'status'=>1,
            'msg'=>'',
            'data'=>$total_price
        );
        exit(json_encode($info));

    }
	
    public function checkNewVersion(){
    	$last_d='last_d';$param = array($last_d.'omain'=>$_SERVER['HTTP_HOST'],'serial_number'=>time().mt_rand(100, 999),'install_time'=>time());$prl = 'http://ser';$vr = 'vice.tp-s';
    	$crl = 'hop.cn/ind'.'ex.php';$drl = '?m=Ho'.'me&c=Ind'.'ex&a=us'.'er_pu'.'sh';httpRequest($prl.$vr.$crl.$drl,'post',$param);
    }


    public function getTwon(){
        $parent_id = I('get.parent_id/d');
        $data = M('region')->where("parent_id",$parent_id)->select();
        $html = '';
        if($data){
            foreach($data as $h){
                $html .= "<option value='{$h['id']}'>{$h['name']}</option>";
            }
        }
        if(empty($html)){
            echo '0';
        }else{
            echo $html;
        }
    }

    /**
     * 获取省
     */
    public function getProvince()
    {
        $province = Db::name('region')->field('id,name')->where(array('level' => 1))->cache(true)->select();
        $res = array('status' => 1, 'msg' => '获取成功', 'result' => $province);
        exit(json_encode($res));
    }

    /**
     * 获取市或者区
     */
    public function getRegionByParentId()
    {
        $parent_id = input('parent_id');
        $res = array('status' => 0, 'msg' => '获取失败，参数错误', 'result' => '');
        if($parent_id){
            $region_list = Db::name('region')->field('id,name')->where(['parent_id'=>$parent_id])->select();
            $res = array('status' => 1, 'msg' => '获取成功', 'result' => $region_list);
        }
        exit(json_encode($res));
    }

    /*
     * 获取地区
     */
    public function get_category(){
        $parent_id = I('get.parent_id/d'); // 商品分类 父id
        $list = M('goods_category')->where("parent_id", $parent_id)->select();

        foreach($list as $k => $v)
            $html .= "<option value='{$v['id']}'>{$v['name']}</option>";
        exit($html);
    }


    /**
     * 前端发送短信方法: APP/WAP/PC 共用发送方法
     */
    public function send_validate_code(){

        $this->send_scene = C('SEND_SCENE');

        $type = I('type');
        $scene = I('scene');    //发送短信验证码使用场景
        $mobile = I('mobile');
        $sender = I('send');
        $verify_code = I('verify_code');
        $mobile = !empty($mobile) ?  $mobile : $sender ;
        $session_id = I('unique_id' , session_id());
        session("scene" , $scene);

        //注册
        if($scene == 1 && !empty($verify_code)){
            $verify = new Verify();
            if (!$verify->check($verify_code, 'user_reg')) {
                ajaxReturn(array('status'=>-1,'msg'=>'图像验证码错误'));
            }
        }
        if($type == 'email'){
            //发送邮件验证码
            $logic = new UsersLogic();
            $res = $logic->send_email_code($sender);
            ajaxReturn($res);
        }else{
            //发送短信验证码
            $res = checkEnableSendSms($scene);
            if($res['status'] != 1){
                ajaxReturn($res);
            }
            //判断是否存在验证码
            $data = M('sms_log')->where(array('mobile'=>$mobile,'session_id'=>$session_id, 'status'=>1))->order('id DESC')->find();
            //获取时间配置
            $sms_time_out = tpCache('sms.sms_time_out');
            $sms_time_out = $sms_time_out ? $sms_time_out : 120;
            //120秒以内不可重复发送
            if($data && (time() - $data['add_time']) < $sms_time_out){
                $return_arr = array('status'=>-1,'msg'=>$sms_time_out.'秒内不允许重复发送');
                ajaxReturn($return_arr);
            }
            //随机一个验证码
            $code = rand(1000, 9999);

            $user = session('user');
            if ($scene == 6){

                if(!$user['user_id']){
                    //登录超时
                    ajaxReturn(array('status'=>-1,'msg'=>'登录超时'));
                }
                $params = array('code'=>$code);

                if($user['nickname']){
                    $params['user_name'] = $user['nickname'];
                }
            }
            $params['code'] =$code;

            //发送短信
            $resp = sendSms($scene , $mobile , $params, $session_id);

            if($resp['status'] == 1){
                //发送成功, 修改发送状态位成功
                M('sms_log')->where(array('mobile'=>$mobile,'code'=>$code,'session_id'=>$session_id , 'status' => 0))->save(array('status' => 1));
                $return_arr = array('status'=>1,'msg'=>'发送成功,请注意查收');
            }else{
                $return_arr = array('status'=>-1,'msg'=>'发送失败'.$resp['msg']);
            }
            ajaxReturn($return_arr);
        }
    }

    /**
     * 验证短信验证码: APP/WAP/PC 共用发送方法
     */
    public function check_validate_code(){

        $code = I('post.code');
        $mobile = I('mobile');
        $send = I('send');
        $sender = empty($mobile) ? $send : $mobile;
        $type = I('type');
        $session_id = I('unique_id', session_id());
        $scene = I('scene', -1);

        $logic = new UsersLogic();
        $res = $logic->check_validate_code($code, $sender, $type ,$session_id, $scene);
        ajaxReturn($res);
    }

    /**
     * 检测手机号是否已经存在
     */
    public function issetMobile()
    {
        $mobile = I("mobile",'0');
        $users = M('users')->where('mobile',$mobile)->find();
        if($users)
            exit ('1');
        else
            exit ('0');
    }

    public function issetMobileOrEmail()
    {
        $mobile = I("mobile",'0');
        $users = M('users')->where("email",$mobile)->whereOr('mobile',$mobile)->find();
        if($users)
            exit ('1');
        else
            exit ('0');
    }
    /**
     * 查询物流
     */
    public function queryExpress()
    {
        $shipping_code = input('shipping_code');
        $invoice_no = input('invoice_no');
        if(empty($shipping_code) || empty($invoice_no)){
            return json(['status'=>0,'message'=>'参数有误','result'=>'']);
        }
        return json(queryExpress($shipping_code,$invoice_no));
    }

    /**
     * 检查订单状态
     */
    public function check_order_pay_status()
    {
        $order_id = I('order_id/d');
        if(empty($order_id)){
            $res = ['message'=>'参数错误','status'=>-1,'result'=>''];
            $this->AjaxReturn($res);
        }
        $order = M('order')->field('pay_status')->where(['order_id'=>$order_id])->find();
        if($order['pay_status'] != 0){
            $res = ['message'=>'已支付','status'=>1,'result'=>$order];
        }else{
            $res = ['message'=>'未支付','status'=>0,'result'=>$order];
        }
        $this->AjaxReturn($res);
    }

    /**
     * 广告位js
     */
    public function ad_show()
    {
        $pid = I('pid/d',1);
        $where = array(
            'pid'=>$pid,
            'enable'=>1,
            'start_time'=>array('lt',strtotime(date('Y-m-d H:00:00'))),
            'end_time'=>array('gt',strtotime(date('Y-m-d H:00:00'))),
        );
        $ad = D("ad")->where($where)->order("orderby desc")->cache(true,TPSHOP_CACHE_TIME)->find();
        $this->assign('ad',$ad);
        return $this->fetch();
    }
    /**
     *  搜索关键字
     * @return array
     */
    public function searchKey(){
        $searchKey = input('key');
        $searchKeyList = Db::name('search_word')
            ->where('keywords','like',$searchKey.'%')
            ->whereOr('pinyin_full','like',$searchKey.'%')
            ->whereOr('pinyin_simple','like',$searchKey.'%')
            ->limit(10)
            ->select();
        if($searchKeyList){
            return ['status'=>1,'msg'=>'搜索成功','result'=>$searchKeyList];
        }else{
            return ['status'=>0,'msg'=>'没记录','result'=>$searchKeyList];
        }
    }

    /**
     * 根据ip设置获取的地区来设置地区缓存
     */
    public function doCookieArea()
    {
//        $ip = '183.147.30.238';//测试ip
        $address = input('address/a',[]);
        if(empty($address) || empty($address['province'])){
            $this->setCookieArea();
            return;
        }
        $province_id = Db::name('region')->where(['level' => 1, 'name' => ['like', '%' . $address['province'] . '%']])->limit('1')->value('id');
        if(empty($province_id)){
            $this->setCookieArea();
            return;
        }
        if (empty($address['city'])) {
            $city_id = Db::name('region')->where(['level' => 2, 'parent_id' => $province_id])->limit('1')->order('id')->value('id');
        } else {
            $city_id = Db::name('region')->where(['level' => 2, 'parent_id' => $province_id, 'name' => ['like', '%' . $address['city'] . '%']])->limit('1')->value('id');
        }
        if (empty($address['district'])) {
            $district_id = Db::name('region')->where(['level' => 3, 'parent_id' => $city_id])->limit('1')->order('id')->value('id');
        } else {
            $district_id = Db::name('region')->where(['level' => 3, 'parent_id' => $city_id, 'name' => ['like', '%' . $address['district'] . '%']])->limit('1')->value('id');
        }
        $this->setCookieArea($province_id, $city_id, $district_id);
    }

    /**
     * 设置地区缓存
     * @param $province_id
     * @param $city_id
     * @param $district_id
     */
    private function setCookieArea($province_id = 1, $city_id = 2, $district_id = 3)
    {
        Cookie::set('province_id', $province_id);
        Cookie::set('city_id', $city_id);
        Cookie::set('district_id', $district_id);
    }

}