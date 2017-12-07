<?php
/**
 * Created by PhpStorm.
 * User: LIKANG
 * Date: 2017/12/6
 * Time: 14:32
 */

class Response
{

    final public function __construct()
    {
        $this->set_debug();
    }

    final public function set_debug()
    {
        $params = DPS::get_instance()->request->get_params();
        if($params["debug"]){
            $this->set_cookie('debug', 1);
        }elseif (isset($params["debug"])){
            //设置立马过期
            $this->set_cookie("debug", 0, time() -1);
        }
    }


    public function set_cookie($key, $value, $expire, $path = "/", $domain = "", $http_only = false)
    {
        if($http_only){
            setcookie($key,$value,$expire,$path,$domain,false,true);
        }else{
            setcookie($key, $value, $expire, $path, $domain);
        }
    }

    public function not_found()
    {
        Header("HTTP/1.1 404 Not Found");
        return false;
    }

    public function status_500()
    {
        Header("HTTP/1.1 500 Invalid Params");
        echo '<h1>非法参数</h1>';
    }

    public function header($key, $value)
    {
        Header("$key:$value");
    }

    public function redirect($location)
    {
        $this->header("Location", $location);
    }

    public function is_https() {
        if($_SERVER['HTTPS']=='on') {
            return true;
        }
        return false;
    }
}