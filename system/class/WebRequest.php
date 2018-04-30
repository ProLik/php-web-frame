<?php

/**
 * Created by PhpStorm.
 * User: pc
 * Date: 2018/4/30
 * Time: 14:30
 */
class WebRequest extends Request
{
    public function get_url(){
        return 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    }


    /**
     * 是否是 手机
     * @return bool
     */
    public function is_mobile() {
        $ua = $this->get_user_agent();
        $pattern1 = '/Profile\/MIDP-\d/i';
        $pattern2 = '/Mozilla\/.*(SymbianOS|iPhone|iTouch|IEMobile|Android|Windows\sCE)/i';
        $isMobile = preg_match($pattern1, $ua) || preg_match($pattern2, $ua);
        if($isMobile) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 是否是iphone
     * @return bool
     */
    public function is_iphone(){
        $agent = strtolower($this->get_user_agent());
        $iphone = (strpos($agent, 'iphone')) ? true : false;
        return $iphone;
    }

    /**
     * 是否是android
     * @return bool
     */
    public function is_android(){
        $agent = strtolower($this->get_user_agent());
        $iphone = (strpos($agent, 'iphone')) ? true : false;
        $android = (strpos($agent, 'android')) ? true : false;
        return $android;
    }

    /**
     * 是否是 微信
     * @return bool
     */
    public function is_weixin() {
        if (strpos($this->get_user_agent(), 'MicroMessenger') !== false) {
            return true;
        } return false;
    }

    /**
     *是否是 IPAD
     * @return bool
     */
    public function is_ipad(){
        $ua = $this->get_user_agent();
        if(strpos($ua,'iPad')!==false) {
            return true;
        } else {
            return false;
        }
    }
}