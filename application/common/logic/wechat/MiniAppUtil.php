<?php
/**
 */

namespace app\common\logic\wechat;

use think\Db;

/**
 * 小程序官方接口类
 */
class MiniAppUtil extends WxCommon
{
    private $config = []; //小程序配置

    public function __construct($config = null)
    {
        if ($config === null) {
            $wxPay = Db::name('plugin')->where(array('type'=>'payment','code'=>'miniAppPay'))->find();
            $config = unserialize($wxPay['config_value']);
        }
        $this->config = $config;
    }
    
    /**
     * 获取小程序session信息
     * @param string $code 登录码
     */
    public function getSessionInfo($code)
    {
        $appId = $this->config['appid'];
        $appSecret = $this->config['appsecret'];
        if (!$appId || !$appSecret) {
            $this->setError('后台还未配置小程序');
            return false;
        }
        
        $fields = [
            'appid' => $appId,
            'secret' => $appSecret,
            'js_code' => $code,
            'grant_type' => 'authorization_code'
        ];
        $url = 'https://api.weixin.qq.com/sns/jscode2session';
        $return = $this->requestAndCheck($url, 'GET', $fields);
        if ($return === false) {
            return false;
        }
        
        return $return;
    }
    
}