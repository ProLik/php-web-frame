<?php
/**
 * 分发器 DispatcherServlet
 * Created by PhpStorm.
 * User: LIKANG
 * Date: 2017/12/6
 * Time: 14:31
 */

class DPS
{
    public $request;
    public $response;
    private $debug_list = array();
    public $debug = true;

    public static $instance;

    public function __construct()
    {
        //设置用户自定义的错误处理函数
        set_error_handler("cf_error_handler");
        $this->debug("Tool loaded");
    }

    public static function get_instance(){
        if(!self::$instance){
            self::$instance = new DPS();
        }
        return self::$instance;
    }

    public function debug($error_str,$title='system') {
        if($this->debug) {
            $this->debug_list[] = array($error_str, $title);
        }
    }

    public function run(){
        $this->debug($_SERVER, 'server');
        $this->debug($_REQUEST, 'request');

        $time_start = microtime(1);

        $this->debug("request");
        if(!$this->request){
            $this->request = new Request();
        }

        $this->debug("response");
        if(!$this->response){
            $this->response = new Response();
        }

        $this->debug("get router");
        $url = $this->request->get_uri_path();
        $this->debug($url);

        $this->debug("get controller");

        $controller = $this->get_controller($url);

        $interceptor_config = $this->get_config('interceptor','interceptor');
        $default_interceptor = $interceptor_config['default'];
        //默认的拦截器，所有的类都要,除了exception里
        if($default_interceptor){
            foreach ($default_interceptor as $interceptor){
                if($exceptions = $interceptor_config['exception'][$interceptor]){
                    if(in_array($controller, $exceptions)){
                        continue;
                    }
                }

                cf_require_interceptor($interceptor);
                $interceptor_class = $interceptor. "Interceptor";
                if(class_exists($interceptor_class)){
                    $this->debug("run interceptor:".$interceptor);
                    $interceptor_obj = new $interceptor_class;
                    if($interceptor_obj->go_next()){
                        continue;
                    }else{
                        $interceptor_obj->broken();
                        exit;
                    }
                }else{
                    continue;
                }


            }
        }

        //特定的拦截器
        $specialed_interceptor = $interceptor_config['specified'];
        $intercepts = $specialed_interceptor[$controller];
        foreach ($intercepts as $interceptor){
            $interceptor_class = $interceptor . "Interceptor";
            cf_require_interceptor($interceptor_class);
            if(class_exists($interceptor_class)){
                $this->debug("run interceptor:" . $interceptor);
                $interceptor_obj = new $interceptor_class;
                if($interceptor_obj->go_next()){
                    continue;
                }else{
                    $interceptor_obj->go_next();
                }
            }else{
                continue;
            }
        }

        $this->debug($controller);

        cf_require_controller($controller);







    }


    private function get_controller($url)
    {
        $router = $this->get_config("router", "router");

        foreach ($router as $k=>$v){
            foreach ($v as $reg){
                $reg = "/" . $reg . "/";
                //执行一个全局正则表达式匹配
                //$result 多维数组，作为输出参数输出所有匹配结果, 数组排序通过flags指定。
                if(preg_match_all($reg, $url, $result)){
                    $matches = array();
                    foreach ($result as $m){
                        foreach ($m as $ma){
                            $matches[] = $ma;
                        }
                    }
                    $this->request->set_matches($matches);
                    return $k;
                }
            }
        }
        return "NotFound";
    }

    /**
     * 获取指定的配置文件
     * @param $key
     * @param string $file
     * @return mixed
     */
    public function get_config($key, $file = "common")
    {
        global $CONFIG_PATH;
        foreach ($CONFIG_PATH as $k => $v){
            $config_path = $v . "/" . $file . ".php";
            if(file_exists($config_path)){
                require $config_path;//why not use requireonce
            }
        }
        return $config[$key];
    }
}