<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/12
 * Time: 10:08
 */

namespace app\mobile\controller;
use think\Db;

class Member extends MobileBase {

    public function goodsList(){
//        $this->isVip();
        return $this->fetch('newhtml/mei');
    }

    public function vipGoodsList(){
        $this->assign('is_vip',$this->isVip());
        $this->assign('key',I('key',''));
        return $this->fetch('index/goodslist');
    }

    public function advisory(){
        return $this->fetch('newhtml/advisory');
    }

    /**
     * 商品详情页
     */
    public function goodsInfo(){
        C('TOKEN_ON',true);
        $goodsLogic = new \app\home\logic\GoodsLogic();
        $goodsModel = new \app\home\model\Goods();
        $goods_id = I("get.id/d");
        $goods = $goodsModel::get($goods_id);
        $goods['discount'] = $goods->discount;
        if(empty($goods) || ($goods['is_on_sale'] == 0)){
            $this->error('此商品不存在或者已下架');
        }
        if (cookie('user_id')) {
            $goodsLogic->add_visit_log(cookie('user_id'), $goods);
        }
        if($goods['brand_id']){
            $brnad = M('brand')->where("id", $goods['brand_id'])->find();
            $goods['brand_name'] = $brnad['name'];
        }
        $goods_images_list = M('GoodsImages')->where("goods_id", $goods_id)->select(); // 商品 图册
        $this->assign('goods_images_list',$goods_images_list);//商品缩略图
        $this->assign('goods',$goods);
        return $this->fetch('index/goodsinfo');
    }
    public function famous_goods_info(){
        C('TOKEN_ON',true);
        $goodsLogic = new \app\home\logic\GoodsLogic();
        $goodsModel = new \app\common\model\Goods();
        $goods_id = I("get.id/d");
        $goods = $goodsModel::get($goods_id);
        $goods['discount'] = $goods->discount;
        if(empty($goods) || ($goods['is_on_sale'] == 0)){
            $this->error('此商品不存在或者已下架');
        }
        if (cookie('user_id')) {
            $goodsLogic->add_visit_log(cookie('user_id'), $goods);
        }
        if($goods['brand_id']){
            $brnad = M('brand')->where("id", $goods['brand_id'])->find();
            $goods['brand_name'] = $brnad['name'];
        }
        $goods_images_list = M('GoodsImages')->where("goods_id", $goods_id)->select(); // 商品 图册
        $this->assign('goods_images_list',$goods_images_list);//商品缩略图
        $this->assign('goods',$goods);
        return $this->fetch('index/famous_goods_info');
    }

    /**
     * @return mixed
     * 异步获取商品//首页推荐商品
     */
    public function ajaxGetMore($is_vip = 0){
        $sort = I('sort','sort'); // 排序
        $sort_asc = I('sort_asc','ASC'); // 排序
        $where = ["is_recommend"=>1,"is_on_sale"=>1,'is_vip'=>$is_vip];
        $p = I('p/d',1);
        $favourite_goods = Db::name('goods')
            ->where($where)
            ->page($p,C('PAGESIZE'))
            ->cache(true,TPSHOP_CACHE_TIME)
            ->order("$sort $sort_asc")
            ->select();
        $this->assign('favourite_goods',$favourite_goods);
        return $this->fetch('newhtml/ajaxGetMore');
    }

    public function buyNow($goods_id = 0){
        $goods = Db::name('goods')->where(['goods_id'=>$goods_id,'is_vip'=>-1])->find();
        !$goods && $this->error('商品错误','viphtml');

        $this->assign('goods', $goods); // 购物车的商品
        return $this->fetch('newhtml/buyNow');
    }

    public function famousGoodsList(){
        $this->assign('is_vip',$this->isVip());
        $this->assign('key',I('key',''));
        return $this->fetch('index/famousGoodsList');
    }

    public function ajaxGetFamousGoods(){
        $sort = I('sort','sort'); // 排序
        $sort_asc = I('sort_asc','ASC'); // 排序
        $where = ["is_on_sale"=>1,'is_vip'=>2];
        $p = I('p/d',1);

        $search_key = I('key',false);
        $search_key && $where['goods_name']=['LIKE','%'.$search_key.'%'];

        $favourite_goods = Db::name('goods')
            ->where($where)
            ->page($p,C('PAGESIZE'))
            ->cache(true,TPSHOP_CACHE_TIME)
            ->order("$sort $sort_asc")
            ->select();

        if(!$favourite_goods) die;
        $this->assign('favourite_goods',$favourite_goods);
        return $this->fetch('index/ajaxGetFamousGoods');
    }

}