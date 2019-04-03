<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/16
 * Time: 9:52
 */
namespace app\mobile\controller;
use app\home\logic\UsersLogic;
use think\Controller;
use think\Db;
use think\Response;
use think\Session;

class MobileBase extends Controller {
    public $session_id;
    public $weixin_config;
    public $cateTrre = array();

    /*
     * 初始化操作
     */
    public function _initialize() {
        session('user'); //不用这个在忘记密码不能获取session('validate_code');
//        Session::start();
        header("Cache-control: private");  // history.back返回后输入框值丢失问题 参考文章 http://www.tp-shop.cn/article_id_1465.html  http://blog.csdn.net/qinchaoguang123456/article/details/29852881
        $this->session_id = session_id(); // 当前的 session_id
        define('SESSION_ID',$this->session_id); //将当前的session_id保存为常量，供其它方法调用
        // 判断当前用户是否手机
//        if(isMobile())
            cookie('is_mobile','1',3600);
//        else
//            cookie('is_mobile','0',3600);

        $this->verifyLogin();

        //微信浏览器
        if(strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')){
            if (session('user.user_id')>0) {
                session('user',null);
                $this->redirect(U('User/reg'));
            }
        }
//            $this->weixin_config = M('wx_user')->find(); //取微获信配置
//            $this->assign('wechat_config', $this->weixin_config);
//            $user_temp = session('user');
//            if (isset($user_temp['user_id']) && $user_temp['user_id']) {
//                $user = M('users')->where("user_id", $user_temp['user_id'])->find();
//                if (!$user) {
//                    $_SESSION['openid'] = 0;
//                    session('user', null);
//                }
//            }
//            if (empty($_SESSION['openid'])){
//                if(is_array($this->weixin_config) && $this->weixin_config['wait_access'] == 1){
//                    $wxuser = $this->GetOpenid(); //授权获取openid以及微信用户信息
//                    session('subscribe', $wxuser['subscribe']);// 当前这个用户是否关注了微信公众号
//                    setcookie('subscribe',$wxuser['subscribe']);
//                    $logic = new UsersLogic();
//                    $is_bind_account = tpCache('basic.is_bind_account');
//                    if ($is_bind_account) {
//                        if (CONTROLLER_NAME != 'User' || ACTION_NAME != 'bind_guide') {
//                            $data = $logic->thirdLogin_new($wxuser);//微信自动登录
//                            if ($data['status'] != 1 && $data['result'] === '100') {
//                                session("third_oauth" , $wxuser);
//                                $first_leader = I('first_leader');
//                                $this->redirect(U('Mobile/User/bind_guide',['first_leader'=>$first_leader]));
//                            }
//                        }
//                    } else {
//                        $data = $logic->thirdLogin($wxuser);
//                    }
//                    if($data['status'] == 1){
//                        session('user',$data['result']);
//                        setcookie('user_id',$data['result']['user_id'],null,'/');
//                        setcookie('is_distribut',$data['result']['is_distribut'],null,'/');
//                        setcookie('uname',$data['result']['nickname'],null,'/');
//                        // 登录后将购物车的商品的 user_id 改为当前登录的id
//                        M('cart')->where("session_id" ,$this->session_id)->save(array('user_id'=>$data['result']['user_id']));
//                        $cartLogic = new \app\home\logic\CartLogic();
//                        $cartLogic->setUserId($data['result']['user_id']);
//                        $cartLogic->doUserLoginHandle();  //用户登录后 需要对购物车 一些操作
//                    }
//                }
//            }else{
//                setcookie('user_id',$user_temp['user_id'],null,'/');
//                setcookie('is_distribut',$user_temp['is_distribut'],null,'/');
//            }
//        }

        $this->public_assign();
    }

    /**
     * 傻逼官方没有封装登录验证函数，还特么每一个都直接跳转
     */
    public function verifyLogin($nologin=[]){
        if(session('?user')){
            $user = session('user');
            $this->user = $user;
            $this->user_id = $user['user_id'];
            $this->assign('user',$user); //存储用户信息
        }
        if(!$this->user_id && !in_array(ACTION_NAME,$nologin)){
            return false;
        }else return true;
    }

    /**
     * 判断是否购买会员
     * @return bool
     */
    public function isVip($status = 0){
        if (session('user.is_vip')){
            if (session('user.time_vip') < strtotime("-1 year")){
                Db::name('users')->update(['user_id'=>session('user.user_id'),'is_vip'=>0]);
                $error = '您的会员已过期，请前往购买会员卡';
            }else{
                return true;
            }
        }else{
            $error = '您尚不是会员用户，请前往购买会员卡';
        }

        if ($status){
            return $this->ajaxReturn(['status'=>-1,'msg'=>$error,'url'=>url('vipHtml')]);
        }else{
            return false;
        }

//        header("location:" . U('vipHtml'));
//        exit;
    }

    /**
     * 会员购买页面
     * @return mixed
     */
    public function vipHtml(){

        if (!$this->verifyLogin()){
            $this->redirect(url('user/login'));
        }

        /*if (session('user.is_vip')){
            $this->redirect(url('member/vipGoodsList'));
//            $this->error('您已是会员用户且未过期，无需重复购买',url('index/index'));
//            header("location:" . U('goodsList'));
//            exit;
        }*/

        $paymentList = Db::name('Plugin')->cache(true)->where("`type`='payment' and status = 1 ")->select();
        $paymentList = convert_arr_key($paymentList, 'code');
        $this->assign('paymentList', $paymentList);

        $favourite_goods = Db::name('goods')
            ->where(["is_on_sale"=>1,'is_vip'=>-1])
            ->cache(true,TPSHOP_CACHE_TIME)
            ->select();
        $this->assign('goods_list',$favourite_goods);

//        return $this->fetch('newhtml/hui');
        return $this->fetch('newhtml/upVipGoodsList');
    }

    public function upVipGoodsHtml($id = 0){
        if (!$this->verifyLogin()){
            $this->redirect(url('user/login'));
        }

//        if (session('user.is_vip')){
//            $this->redirect(url('member/vipGoodsList'));
//                        $this->error('您已是会员用户且未过期，无需重复购买');
            //            header("location:" . U('goodsList'));
            //            exit;
//        }

        $goodsModel = new \app\home\model\Goods();
        $goods = $goodsModel::get($id);
        if(empty($goods) || ($goods['is_on_sale'] == 0)){
            $this->error('此商品不存在或者已下架');
        }
        $goods['sale_num'] = M('order_goods')->where(['goods_id'=>$id,'is_send'=>1])->count();

        $goods_images_list = M('GoodsImages')->where("goods_id", $id)->select(); // 商品 图册
        $this->assign('goods_images_list',$goods_images_list);//商品缩略图
        $this->assign('goods',$goods);
        return $this->fetch('newhtml/upVipGoods');
    }

    /**
     * 会员购买逻辑
     */
    public function paying(){
        $user = session('user');
        $map['user_id'] = $user['user_id'];
        $map['buy_vip'] = 1;
        $map['pay_status'] = 1;
//        $info = Db::name('recharge')->where($map)->order('order_id desc')->find();
        if ($user['is_vip'] == 1) {
            $this->error('您已是会员用户且未过期，无需重复充值办理该业务！');
        }

        $data['user_id']    = $this->user_id;
        $data['nickname']   = $user['nickname'];
//        $data['account']    = config('vipprice');
        $data['account']    = tpCache('distribut.vip_price');
        $data['order_sn']   = 'recharge' . get_rand_str(10, 0, 1);
        $data['buy_vip']    = 1;
        $data['ctime']  = time();
        $order_id = Db::name('recharge')->add($data);

        if ($order_id) {
            $url = U('Mobile/Payment/getPay', array('pay_radio' => $_REQUEST['pay_radio'], 'order_id' => $order_id));
            $this->redirect($url);
        } else {
            $this->error('提交失败,参数有误!');
        }
    }

    /**
     * 保存公告变量到 smarty中 比如 导航
     */
    public function public_assign()
    {
        $first_login = session('first_login');
        $this->assign('first_login', $first_login);
        if (!$first_login && ACTION_NAME == 'login') {
            session('first_login', 1);
        }

        $tpshop_config = array();
        $tp_config = M('config')->cache(true,TPSHOP_CACHE_TIME)->select();
        foreach($tp_config as $k => $v)
        {
            if($v['name'] == 'hot_keywords'){
                $tpshop_config['hot_keywords'] = explode('|', $v['value']);
            }
            $tpshop_config[$v['inc_type'].'_'.$v['name']] = $v['value'];
        }

        $goods_category_tree = get_goods_category_tree();
        $this->cateTrre = $goods_category_tree;
        $this->assign('goods_category_tree', $goods_category_tree);
        $brand_list = M('brand')->cache(true,TPSHOP_CACHE_TIME)->field('id,cat_id,logo,is_hot')->where("cat_id>0")->select();
        $this->assign('brand_list', $brand_list);
        $this->assign('tpshop_config', $tpshop_config);
        $this->assign('user_id',cookie('user_id'));
    }

    // 网页授权登录获取 OpendId
    public function GetOpenid()
    {
        if($_SESSION['openid'])
            return $_SESSION['openid'];
        //通过code获得openid
        if (!isset($_GET['code'])){
            //触发微信返回code码
            //$baseUrl = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
            $baseUrl = urlencode($this->get_url());
            $url = $this->__CreateOauthUrlForCode($baseUrl); // 获取 code地址
            Header("Location: $url"); // 跳转到微信授权页面 需要用户确认登录的页面
            exit();
        } else {
            //上面获取到code后这里跳转回来
            $code = $_GET['code'];
            $data = $this->getOpenidFromMp($code);//获取网页授权access_token和用户openid
            $data2 = $this->GetUserInfo($data['access_token'],$data['openid']);//获取微信用户信息
            $data['nickname'] = empty($data2['nickname']) ? '微信用户' : trim($data2['nickname']);
            $data['sex'] = $data2['sex'];
            $data['head_pic'] = $data2['headimgurl'];
            $data['subscribe'] = $data2['subscribe'];
            $_SESSION['openid'] = $data['openid'];
            $data['oauth'] = 'weixin';
            if(isset($data2['unionid'])){
                $data['unionid'] = $data2['unionid'];
            }
            return $data;
        }
    }

    /**
     * 获取当前的url 地址
     * @return type
     */
    private function get_url() {
        $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
        $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
        return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
    }

    /**
     *
     * 通过code从工作平台获取openid机器access_token
     * @param string $code 微信跳转回来带上的code
     *
     * @return openid
     */
    public function GetOpenidFromMp($code)
    {
        //通过code获取网页授权access_token 和 openid 。网页授权access_token是一次性的，而基础支持的access_token的是有时间限制的：7200s。
        //1、微信网页授权是通过OAuth2.0机制实现的，在用户授权给公众号后，公众号可以获取到一个网页授权特有的接口调用凭证（网页授权access_token），通过网页授权access_token可以进行授权后接口调用，如获取用户基本信息；
        //2、其他微信接口，需要通过基础支持中的“获取access_token”接口来获取到的普通access_token调用。
        $url = $this->__CreateOauthUrlForOpenid($code);
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);//设置超时
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $res = curl_exec($ch);//运行curl，结果以jason形式返回
        $data = json_decode($res,true);
        curl_close($ch);
        return $data;
    }


    /**
     *
     * 通过access_token openid 从工作平台获取UserInfo
     * @return openid
     */
    public function GetUserInfo($access_token,$openid)
    {
        // 获取用户 信息
        $url = $this->__CreateOauthUrlForUserinfo($access_token,$openid);
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);//设置超时
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $res = curl_exec($ch);//运行curl，结果以jason形式返回
        $data = json_decode($res,true);
        curl_close($ch);
        //获取用户是否关注了微信公众号， 再来判断是否提示用户 关注
        if(!isset($data['unionid'])){
            $access_token2 = $this->get_access_token();//获取基础支持的access_token
            $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access_token2&openid=$openid";
            $subscribe_info = httpRequest($url,'GET');
            $subscribe_info = json_decode($subscribe_info,true);
            $data['subscribe'] = $subscribe_info['subscribe'];
        }
        return $data;
    }


    public function get_access_token(){
        //判断是否过了缓存期
        $expire_time = $this->weixin_config['web_expires'];
        if($expire_time > time()){
            return $this->weixin_config['web_access_token'];
        }
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->weixin_config[appid]}&secret={$this->weixin_config[appsecret]}";
        $return = httpRequest($url,'GET');
        $return = json_decode($return,1);
        $web_expires = time() + 7140; // 提前60秒过期
        M('wx_user')->where(array('id'=>$this->weixin_config['id']))->save(array('web_access_token'=>$return['access_token'],'web_expires'=>$web_expires));
        return $return['access_token'];
    }

    /**
     *
     * 构造获取code的url连接
     * @param string $redirectUrl 微信服务器回跳的url，需要url编码
     *
     * @return 返回构造好的url
     */
    private function __CreateOauthUrlForCode($redirectUrl)
    {
        $urlObj["appid"] = $this->weixin_config['appid'];
        $urlObj["redirect_uri"] = "$redirectUrl";
        $urlObj["response_type"] = "code";
//        $urlObj["scope"] = "snsapi_base";
        $urlObj["scope"] = "snsapi_userinfo";
        $urlObj["state"] = "STATE"."#wechat_redirect";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;
    }

    /**
     *
     * 构造获取open和access_toke的url地址
     * @param string $code，微信跳转带回的code
     *
     * @return 请求的url
     */
    private function __CreateOauthUrlForOpenid($code)
    {
        $urlObj["appid"] = $this->weixin_config['appid'];
        $urlObj["secret"] = $this->weixin_config['appsecret'];
        $urlObj["code"] = $code;
        $urlObj["grant_type"] = "authorization_code";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://api.weixin.qq.com/sns/oauth2/access_token?".$bizString;
    }

    /**
     *
     * 构造获取拉取用户信息(需scope为 snsapi_userinfo)的url地址
     * @return 请求的url
     */
    private function __CreateOauthUrlForUserinfo($access_token,$openid)
    {
        $urlObj["access_token"] = $access_token;
        $urlObj["openid"] = $openid;
        $urlObj["lang"] = 'zh_CN';
        $bizString = $this->ToUrlParams($urlObj);
        return "https://api.weixin.qq.com/sns/userinfo?".$bizString;
    }

    /**
     *
     * 拼接签名字符串
     * @param array $urlObj
     *
     * @return 返回已经拼接好的字符串
     */
    private function ToUrlParams($urlObj)
    {
        $buff = "";
        foreach ($urlObj as $k => $v)
        {
            if($k != "sign"){
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }


//    public function ajaxReturn($data){
//        exit(json_encode($data));
//    }

    protected function ajaxReturn($r_data = []){
        $header = [
            'Content-Type'=>'application/json; charset=utf-8',
            'Access-Control-Allow-Credentials'=>'true',
            'Access-Control-Allow-Origin'=>'*',
        ];

        Response::create($r_data,'json',200,$header)->send();
        die;
    }
}