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


    }

}