<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/21
 * Time: 17:32
 */

namespace app\mobile\controller;
use app\home\logic\UsersLogic;
use think\Controller;
use think\Response;

class Register extends Controller{


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

    /**
     *  注册
     */
    public function reg()
    {

        if (session('user.user_id')>0) {
//            $this->redirect(U('Mobile/User/index'));
//            $this->error(['status'=>1,'msg'=>'您已注册']);
            session('user',null);
        }
        $reg_sms_enable  = tpCache('sms.regis_sms_enable');
        $reg_smtp_enable = tpCache('sms.regis_smtp_enable');

        if (IS_POST) {
            $logic = new UsersLogic();
            //验证码检验
            //$this->verifyHandle('user_reg');
            $username  = I('post.username', '');
            $password  = I('post.password', '');
            $password2 = I('post.password2', '');
            //是否开启注册验证码机制
            $code  = I('post.mobile_code', '');
            $scene = I('post.scene', 1);

            $session_id = session_id();

            //是否开启注册验证码机制
            if (check_mobile($username)) {
                if ($reg_sms_enable) {
                    //手机功能没关闭
                    $check_code = $logic->check_validate_code($code, $username, 'phone', $session_id, $scene);
                    if ($check_code['status'] != 1) {
                        $this->ajaxReturn($check_code);
                    }
                }
            }

//            //是否开启注册邮箱验证码机制
//            if(check_email($username)){
//                if($reg_smtp_enable){
//                    //邮件功能未关闭
//                    $check_code = $logic->check_validate_code($code, $username);
//                    if($check_code['status'] != 1){
//                        $this->ajaxReturn($check_code);
//                    }
//                }
//            }

            $distribut = I('distribut', 0);
            if (!$distribut) $this->error('404');

            $data = $logic->reg($username, $password, $password2, $distribut);
            if ($data['status'] != 1)
                $this->ajaxReturn($data);
            session('user', $data['result']);
            setcookie('user_id', $data['result']['user_id'], null, '/');
            setcookie('is_distribut', $data['result']['is_distribut'], null, '/');
            $cartLogic = new \app\home\logic\CartLogic();
            $cartLogic->setUserId($data['result']['user_id']);
            $cartLogic->doUserLoginHandle();// 用户登录后 需要对购物车 一些操作
//            $cartLogic->login_cart_handle($this->session_id, $data['result']['user_id']);  //用户登录后 需要对购物车 一些操作
            $this->ajaxReturn($data);
            exit;
        }
        $this->assign('regis_sms_enable', $reg_sms_enable); // 注册启用短信：
        $this->assign('regis_smtp_enable', $reg_smtp_enable); // 注册启用邮箱：
        $sms_time_out = tpCache('sms.sms_time_out') > 0 ? tpCache('sms.sms_time_out') : 120;
        $this->assign('sms_time_out', $sms_time_out); // 手机短信超时时间

        return $this->fetch('user/reg');
    }
}