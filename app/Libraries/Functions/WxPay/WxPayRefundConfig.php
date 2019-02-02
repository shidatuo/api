<?php
/**
 * Class WxPayRefundConfig
 * @author shidatuo
 * @description 配置退款账户信息
 */
class WxPayRefundConfig{
    //=======【基本信息设置】=====================================
    //
    /**
     * TODO: 修改这里配置为您自己申请的商户信息
     * 微信公众号信息配置
     *
     * APPID：绑定支付的APPID（必须配置，开户邮件中可查看）
     *
     * MCHID：商户号（必须配置，开户邮件中可查看）
     *
     * KEY：商户支付密钥，参考开户邮件设置（必须配置，登录商户平台自行设置）
     * 设置地址：https://pay.weixin.qq.com/index.php/account/api_cert
     *
     * APPSECRET：公众帐号secert（仅JSAPI支付的时候需要配置， 登录公众平台，进入开发者中心可设置），
     * 获取地址：https://mp.weixin.qq.com/advanced/advanced?action=dev&t=advanced/dev&token=2005451881&lang=zh_CN
     * @var string
     */
    static public $APPID = '';
    static public $MCHID = '';
    static public $KEY = '';

    //=======【证书路径设置】=====================================
    /**
     * TODO：设置商户证书路径
     * 证书路径,注意应该填写绝对路径（仅退款、撤销订单时需要，可登录商户平台下载，
     * API证书下载地址：https://pay.weixin.qq.com/index.php/account/api_cert，下载之前需要安装商户操作证书）
     * @var path
     */
    static public $SSLCERT_PATH = '';
    static public $SSLKEY_PATH = '';

    //=======【curl代理设置】===================================
    /**
     * TODO：这里设置代理机器，只有需要代理的时候才设置，不需要代理，请设置为0.0.0.0和0
     * 本例程通过curl使用HTTP POST方法，此处可修改代理服务器，
     * 默认CURL_PROXY_HOST=0.0.0.0和CURL_PROXY_PORT=0，此时不开启代理（如有需要才设置）
     * @var unknown_type
     */
    static public $CURL_PROXY_HOST = "0.0.0.0";//"10.152.18.220";
    static public $CURL_PROXY_PORT = 0;//8080;

    //=======【上报信息配置】===================================
    /**
     * TODO：接口调用上报等级，默认紧错误上报（注意：上报超时间为【1s】，上报无论成败【永不抛出异常】，
     * 不会影响接口调用流程），开启上报之后，方便微信监控请求调用的质量，建议至少
     * 开启错误上报。
     * 上报等级，0.关闭上报; 1.仅错误出错上报; 2.全量上报
     * @var int
     */
    static public $REPORT_LEVENL = 1;

    //=======【服务商退款设置】===================================
    /**
     * @link https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_sl_api.php?chapter=9_4
     */
    static public $FWAPPID = '';//服务商appId
    static public $FWMCHID = '';//服务商商户号;
    static public $SUBAPPID = '';//小程序的APPID;
    static public $SUBMCHID = '';//子商户号;

    static public function setConfig($cfg)
    {
        self::$APPID = isset($cfg['AppId']) ? $cfg['AppId'] : '';
        self::$KEY = isset($cfg['wx_v3_key']) ? $cfg['wx_v3_key'] : '';
        self::$MCHID = isset($cfg['wx_v3_mhcid']) ? $cfg['wx_v3_mhcid'] : '';
        self::$SSLCERT_PATH = isset($cfg['wx_v3_apiclient_cert_path']) ? $cfg['wx_v3_apiclient_cert_path'] : '';
        self::$SSLKEY_PATH = isset($cfg['wx_v3_apiclient_key_path']) ? $cfg['wx_v3_apiclient_key_path'] : '';
        ### 快速认证的必传参数
        self::$FWAPPID    = isset($cfg['appid'])?$cfg['appid']:'';
        self::$FWMCHID    = isset($cfg['mch_id'])?$cfg['mch_id']:'';
        self::$SUBAPPID   = isset($cfg['sub_appid'])?$cfg['sub_appid']:'';
        self::$SUBMCHID    = isset($cfg['sub_mch_id'])?$cfg['sub_mch_id']:'';
    }
}