<?php
/**
 * Created by PhpStorm.
 * User: LIKANG
 * Date: 2017/12/22
 * Time: 18:00
 */

abstract class View
{
    public $data;

    public function get_title()
    {
        $dps = DPS::get_instance();
        return $dps->get_config("name") . $dps->get_config("version");
    }

    public function get_keywords()
    {

    }

    public function get_description()
    {

    }

    public function build_container()
    {
        DPS::get_instance()->debug("build_html_container", $this->get_container());
    }

    public function build_content()
    {
        DPS::get_instance()->debug("build_html_content");
        $this->include_template($this->get_content());
    }

    abstract public function get_content();

    public function get_container()
    {

    }

    public function include_template($name, $view = "view", $type = "phtml")
    {
        extract($this->data);
        $path = self::get_real_path($name, $view, $type);
        if($path){
            require $path;
        }else{
            DPS::get_instance()->debug("Not Found Template,Name:{$name},View:{$view},Type:{$type}", "DPS ERROR");
        }
    }

    public static function get_real_path($name, $view = "view", $type = "phtml")
    {
        $path = cf_build_path($name, $view);
        $path = CUR_PATH . $path . "." . $type;
        if(file_exists($path)){
            return $path;
        }

        $path = cf_build_path($name, $view);
        $path = SYS_PATH . $path . "." . $type;

        if(file_exists($path)){
            return $path;
        }

        global $INCLUDE_PATH;
        if($INCLUDE_PATH) {
            foreach($INCLUDE_PATH as $path_root) {
                $path  = cf_build_path($name, $view);
                $path = $path_root . $path.'.'.$type;
                if(file_exists($path)) {
                    return $path;
                }
            }
        }
        return false;
    }

    public function set_data($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function get_class_name()
    {
        $called = get_called_class();
        return substr($called, 0, -4);
    }

    public function build_css_url()
    {
        $request = DPS::get_instance()->get_request();
        $source_url = ConfigTool::get_instance()->get_config("source");

        $location = ConfigTool::get_instance()->get_config("location");

        $class_name = $this->get_class_name();

        if(strpos($class_name, "\\") !== false){
            $class_name = str_replace("\\", "/", $class_name);
        }

        $real_url = $source_url.$location."/resource/css/".$class_name.".css";

        return $real_url."?v=".VERSION."&".($request->is_https()?"https":"");
    }


    public function build_js_url ()
    {
        $source_url = ConfigTool::get_instance()->get_config("source");


        $location = ConfigTool::get_instance()->get_config("location");

        $class_name = $this->get_class_name();

        if(strpos($class_name,'\\')!==false) {
            $class_name = str_replace('\\','/',$class_name);
        }

        $real_url = $source_url.$location.'/resource/js/'.$class_name.'.js';
        $request = DPS::get_instance()->get_request();
        return $real_url.'?v='.VERSION.'&'.($request->is_https()?'https':'');
    }

    public static function get_css_list()
    {
        return array();
    }

    public static function get_js_list() {
        return array(
            'RSF'
        );
    }

    public static function get_static_js_list() {
        return array(

        );
    }
    public static function get_plugin() {
        return array();
    }

    public function get_css_header()
    {
        $called = get_called_class();
        $css_list = $called::get_css_list();
        $tag = "";
        $last_modify = 0;
        foreach ($css_list as $v){
            $real_path = self::get_real_path($v,'view','css');
            //取得文件修改时间
            $last_modify_time = filemtime($real_path);
            $tag .= $v . $last_modify_time . "view";
            $last_modify = max($last_modify, $last_modify_time);
        }

        $plugin_list = $called::get_plugin();
        foreach ($plugin_list as $key => $plugin){
            $real_name = $plugin.'Plugin';
            $css_list = $real_name::get_css_list();
            foreach ($css_list as $v){
                $real_path = self::get_real_path($v,'plugin','css');
                $last_modify_time = filemtime($real_path);
                $tag .= $v.$last_modify_time.'view';
                $last_modify = max($last_modify,$last_modify_time);
            }
        }
        $request = DPS::get_instance()->get_request();
        $tag .= serialize($request->get_params());

        $tag  = md5(($request->is_https()?'https':'').$tag.VERSION.'css');
        $header = array('etag'=>$tag,'last_mod'=>$last_modify);
        return $header;
    }

    public function get_css_content($header, $host = "")
    {
        $cache_key = $host . $header["etag"];
        $content = "";
        if(ConfigTool::get_instance()->get_config("cache")){
            $cache = DBTool::get_instance()->get_cache();
            $cache_content = $cache->get($cache_key);
            if($cache_content){
                DPS::get_instance()->get_response()->header("from mem", 1);
                $content = $cache_content;
                echo $content;
            }
        }

        if(!$content){
            $called = get_called_class();
            $css_list = $called::get_css_list();

            foreach ($css_list as $v){
                $this->begain_script_block();
                $this->include_template($v, "view", "css");
                $this->end_script_block();
            }
        }
    }





    public function auto_make_css_size($content, $screen_width, $psd_width)
    {
        //执行一个正则表达式搜索并且使用一个回调进行替换
        return preg_replace_callback('/\/\*real\{([\-\d]+)\}\*\//',function($matches) use($screen_width,$psd_width) {
            return $this->get_real_px($matches[1],$screen_width,$psd_width).'px';
        },$content);
    }

    public static function get_real_px($px, $screen_width, $psd_width)
    {
        if( $px == '1') {
            return $px;
        }

        $psd_width = $psd_width ? $psd_width : 720;
        return  round($screen_width/ $psd_width*$px);
    }
}