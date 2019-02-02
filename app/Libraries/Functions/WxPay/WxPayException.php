<?php
/**
 * Class WxPayException
 * @author shidatuo
 * @description 微信支付API异常类
 */
class WxPayException extends \Exception {

    /**
     * @return string
     * @author shidatuo
     * @description 抛出异常类
     */
    public function errorMessage(){
        return $this->getMessage();
    }
}