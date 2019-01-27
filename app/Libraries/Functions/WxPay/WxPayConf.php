<?php
/**
 * Class WxPayConf
 * @author shidatuo
 * @description 配置账号信息
 */
class WxPayConf {
    static public $APPID;
    static public $APPSECRET;
    static public $MCHID;
    static public $KEY;
    static public $CURL_TIMEOUT;
    static public $NOTIFY_URL;
    static public $RETURN_URL;
    static public  $FWAPPID;
    static public  $FWMCHID;
    public function __construct($wxpayconfig = array()) {
        self::$APPID = isset($wxpayconfig['appid']) ? $wxpayconfig['appid'] : '';
        self::$APPSECRET = isset($wxpayconfig['appsecret']) ? $wxpayconfig['appsecret'] : '';
        self::$MCHID = isset($wxpayconfig['mchid']) ? $wxpayconfig['mchid'] : '';
        self::$KEY = isset($wxpayconfig['key']) ? $wxpayconfig['key'] : '';
        self::$CURL_TIMEOUT = 120;
        self::$NOTIFY_URL = isset($wxpayconfig['notifyurl']) ? $wxpayconfig['notifyurl'] : '';
        self::$RETURN_URL = isset($wxpayconfig['returnurl']) ? $wxpayconfig['returnurl'] : '';
        self::$FWAPPID    = isset($wxpayconfig['fwappid'])?$wxpayconfig['fwappid']:'';
        self::$FWMCHID    = isset($wxpayconfig['fwmchid'])?$wxpayconfig['fwmchid']:'';
    }
}