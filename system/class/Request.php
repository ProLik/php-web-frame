<?php
/**
 * Created by PhpStorm.
 * User: LIKANG
 * Date: 2017/12/6
 * Time: 14:32
 */

class Request
{

    private $matches;
    public $is_debug;
    public $attr = array();
    public $uid;


    //PHP 5 新增了一个 final 关键字。
    //如果父类中的方法被声明为 final，则子类无法覆盖该方法。
    //如果一个类被声明为 final，则不能被继承。
    final public function __construct()
    {
        $this->is_debug = $this->is_debug();
    }

    final public function is_debug()
    {
        $params = $this->get_params();
        if(isset($params["debug"]) && $params["debug"] == 1){
            return true;
        }
        if($this->get_cookie("debug") == 1){
            return true;
        }
        return false;
    }

    public function get_param($key, $default_value = '')
    {
        $params = $this->get_params();
        if(!isset($params[$key]) || empty($params[$key])){
            return $default_value;
        }
        return $params[$key];
    }

    public function get_params()
    {
        $params = array();
        foreach ($_GET as $k => $v){
            $params[$k] = $v;
        }
        foreach ($_POST as $k => $v){
            $params[$k] = $v;
        }
        return $params;
    }

    public function get_cookie($key)
    {
        return $_COOKIE[$key];
    }

    public function get_cookies()
    {
        return $_COOKIE;
    }

    public function get_uri_path()
    {
        $uri_array = explode("?" , $_SERVER["REQUEST_URI"]);
        return $uri_array[0];
    }

    public function get_domain()
    {
        return $_SERVER["HTTP_HOST"];
    }

    public function get_client_ip()
    {
        return $_SERVER["REMOTE_ADDR"];
    }

    public function get_matches()
    {
        return $this->matches;
    }

    public function set_matches($matches)
    {
        $this->matches = $matches;
    }

    public function get_attributes(){
        return $this->attr;
    }

    public function set_attributes($key, $value)
    {
        $this->attr[$key] = $value;
    }

    public function get_attribute($key){
        return $this->attr[$key];
    }

    public function get_uid()
    {
        return $this->uid;
    }

    public function set_uid($uid)
    {
        $this->uid = $uid;
    }

    public function get_user_agent()
    {
        return $_SERVER["HTTP_USER_AGENT"];
    }

    /**
     * 获取前一页面的 URL 地址
     * @return mixed
     */
    public function get_referer()
    {
        return $_SERVER["HTTP_REFERER"];
    }

    public function is_post(){
        return $_POST;
    }

    public function is_https()
    {
        if($_SERVER["HTTPS"] == "on"){
            return true;
        }
        return false;
    }



}