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
        //防止 cdn将https和http缓存成同一个请求
        return $real_url.'?v='.VERSION.'&'.($request->is_https()?'https':'');
    }

}