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
    public $debug;
    private static $instance;

    public function __construct()
    {
        //设置用户自定义的错误处理函数
        set_error_handler("cf_error_handler");
        cf_require_class("DebugTool");
        DebugTool::get_instance()->debug("DPS loaded");
    }

    public static function get_instance(){
        if(!self::$instance){
            self::$instance = new DPS();

        }
        return self::$instance;
    }


    public function run(){
        cf_require_class("ConfigTool");

        DebugTool::get_instance()->debug($_SERVER, 'server');
        DebugTool::get_instance()->debug($_REQUEST, 'request');

        $time_start = microtime(1);

        DebugTool::get_instance()->debug("request");
        if(!$this->request){
            $this->request = new Request();
        }

        DebugTool::get_instance()->debug("response");
        if(!$this->response){
            $this->response = new Response();
        }

        DebugTool::get_instance()->debug("get router");
        $url = $this->request->get_uri_path();
        DebugTool::get_instance()->debug($url);

        DebugTool::get_instance()->debug("get controller");

        $controller = $this->get_controller($url);

        $interceptor_config = ConfigTool::get_instance()->get_config('interceptor','interceptor');
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
                    DebugTool::get_instance()->debug("run interceptor:".$interceptor);
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
        if($intercepts != null){
            foreach ($intercepts as $interceptor){
                $interceptor_class = $interceptor . "Interceptor";
                cf_require_interceptor($interceptor_class);
                if(class_exists($interceptor_class)){
                    DebugTool::get_instance()->debug("run interceptor:" . $interceptor);
                    $interceptor_obj = new $interceptor_class;
                    if($interceptor_obj->go_next()){
                        continue;
                    }else{
                        $interceptor_obj->broken();
                    }
                }else{
                    continue;
                }
            }
        }
        DebugTool::get_instance()->debug($controller);

        cf_require_controller($controller);

        $controller_class = $controller . "Controller";
        $controller_obj = new $controller_class;
        DebugTool::get_instance()->debug("controller run");

        $c_s = microtime(1);
        $view = $controller_obj->run();

        DebugTool::get_instance()->debug('Controller time coast:'.(microtime(1)-$c_s).'s');

        if($view){
            cf_require_view($view);

            $view_class = $view . "View";
            $view_obj = new $view_class;
            DebugTool::get_instance()->debug("build html");
            $view_obj->build_container();
        }

        DebugTool::get_instance()->debug('All time coast:'.(microtime(1)-$time_start).'s');
        if($this->request->is_debug){
            DebugTool::get_instance()->show_debug_message();
        }

    }


    private function get_controller($url)
    {
        $router = ConfigTool::get_instance()->get_config("router", "router");

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



    public function get_request()
    {
        return $this->request;
    }

    public function set_request($request)
    {
        $this->request = $request;
    }

    public function get_response()
    {
        return $this->response;
    }

    public function set_response($response)
    {
        $this->response = $response;
    }
}