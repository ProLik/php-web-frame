<?php
/**
 * Created by PhpStorm.
 * User: LIKANG
 * Date: 2017/12/22
 * Time: 18:00
 */

abstract class View
{
    public $data = array();

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
        DebugTool::get_instance()->debug("build_html_container", $this->get_container());
        $this->include_template($this->get_container());
    }

    public function build_content()
    {
        DebugTool::get_instance()->debug("build_html_content");
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
            DebugTool::get_instance()->debug("Not Found Template,Name:{$name},View:{$view},Type:{$type}", "DPS ERROR");
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
            'DPS'
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

        //插件内的css
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
                DPS::get_instance()->get_response()->header("from_mem", 1);
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

            //插件内的CSS
            $plugin_list = $called::get_plugin();
            foreach($plugin_list as $key=>$plugin){
                $real_name = $plugin . "Plugin";
                $css_list = $real_name::get_css_list();
                foreach ($css_list as $v) {
                    $this->begain_script_block();
                    $this->include_template($v, 'plugin', 'css');
                    $this->end_script_block();
                }
            }

            $content = implode('', $this->script_blocks);
            $screen_width = DPS::get_instance()->get_request()->get_param("sw");
            if($screen_width){
                $psd_width = DPS::get_instance()->get_request()->get_param("psw", 720);
                $content = $this->auto_make_css_size($content, $screen_width, $psd_width);
            }



            if(ConfigTool::get_instance()->get_config("compress_css")){
                $url = ConfigTool::get_instance()->get_config("compress_server");
                $post_data = array("css" => $content);
                $query = http_build_query(["key" => $cache_key]);

                $c = Tool::post($url."?".$query, $post_data);
                if($c){
                    $content = $c;
                }
            }

            if(ConfigTool::get_instance()->get_config("cache")){
                $cache = DBTool::get_instance()->get_cache();
                $cache->set($cache_key, $content, 0);
            }

            echo $content;
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

    public function get_js_content_header() {
        $called = get_called_class();
        $common_js_list = $called::get_js_list();
        //插件内的js
        $called = get_called_class();
        $plugin_list = $called::get_plugin();
        $plugin_js_list = array();
        foreach ($plugin_list as $key => $plugin) {
            $real_name = $plugin.'Plugin';
            $js_list = $real_name::get_js_list();
            foreach($js_list as $v) {
                $plugin_js_list[] = $v;
            }
        }
        $key = '';
        $last_mod = 0;
        $last_time_list = [];
        foreach($common_js_list as $v) {
            $real_path = self::get_real_path($v,'view','js');
            $last_mod_time = filemtime($real_path);
            $last_time_list[] = $last_mod_time;
            $key .= $v.$last_mod_time.'view';
            $last_mod = max($last_mod,$last_mod_time);
        }
        DPS::get_instance()->get_response()->header('js_list',implode(',',$common_js_list));
        DPS::get_instance()->get_response()->header('time_list',implode(',',$last_time_list));

        foreach($plugin_js_list as $v) {
            $real_path = self::get_real_path($v,'plugin','js');
            $last_mod_time = filemtime($real_path);
            $key .= $v.$last_mod_time.'plugin';
            $last_mod = max($last_mod,$last_mod_time);
        }


        $request = DPS::get_instance()->get_request();
        $key  = md5(($request->is_https()?'https':'').$key.VERSION.'js');

        return array('etag'=>$key,'last_mod'=>$last_mod);
    }
    public function get_js_content($head,$host='') {
        $tag  = $host.$head['etag'];
        $content = '';
        if(ConfigTool::get_instance()->get_config("cache")) {
            $cache = DBTool::get_instance()->get_cache();
            $result = $cache->get($tag);
            if ($result) {
                DPS::get_instance()->get_response()->header('from_mem', 1);
                $content =  $result;
                echo $content;
            }
        }
        if(!$content){
            $called = get_called_class();
            $common_js_list = $called::get_js_list();
            //插件内的js
            $called = get_called_class();
            $plugin_list = $called::get_plugin();
            $plugin_js_list = array();
            foreach ($plugin_list as $key => $plugin) {
                $real_name = $plugin.'Plugin';
                $js_list = $real_name::get_js_list();
                foreach($js_list as $v) {
                    $plugin_js_list[] = $v;
                }
            }
            $this->begain_script_block();
            foreach($common_js_list as $v) {
                $this->include_template($v,'view','js');
                echo ';';echo PHP_EOL;
            }
            foreach($plugin_js_list as $v) {
                $this->include_template($v,'plugin','js');
                echo ';';echo PHP_EOL;
            }
            $this->end_script_block();
            $content = implode('', $this->script_blocks);
            if (ConfigTool::get_instance()->get_config('compress_js')) {
                $url = ConfigTool::get_instance()->get_config('compress_server');
                $post_data = array('js' => $content);
                $query = http_build_query([
                    'key'=>$tag
                ]);
                $c = Tool::post($url.'?'.$query, $post_data);
                if($c) {
                    $content = $c;
                }
            }
            if(ConfigTool::get_instance()->get_config("cache")) {
                $cache = DBTool::get_instance()->get_cache();
                $cache->set($tag, $content, 0);
            }
            echo $content;
        }

    }
    private $dependence_js_list = array();

    public function require_js($js){
        if(!$this->dependence_js_list[$js]) {
            echo PHP_EOL.'//dependence:' . $js . PHP_EOL;
            $this->include_template($js, 'view', 'js');
            $this->dependence_js_list[$js] = 1;
        } else {
            echo PHP_EOL.'//repeat_dependence:' . $js . PHP_EOL;
        }
    }


    private $dependence_css_list = array();
    public function require_css($css){
        if(!$this->dependence_css_list[$css]) {
            echo PHP_EOL.'//dependence:' . $css . PHP_EOL;
            $this->include_template($css, 'view', 'css');
            $this->dependence_css_list[$css] = 1;
        } else {
            echo PHP_EOL.'//repeat_dependence:' . $css . PHP_EOL;
        }
    }


    //为了把script的执行代码全部放在最后，so
    public function begain_script_block() {
        ob_start();
    }
    public function end_script_block() {
        $this->add_script_blocks(ob_get_contents());
        ob_end_clean();
    }
    private $script_blocks = array();
    public function add_script_blocks($str) {
        $this->script_blocks[] = $str;
    }
    public function write_script_blocks() {
        foreach($this->script_blocks as $v) {
            echo $v;
        }
    }

    public function write_script_blocks_with_out_script_tag() {
        foreach($this->script_blocks as $v) {
            echo strip_tags($v);
        }
    }
}