<?php

/**
 * Created by PhpStorm.
 * User: pc
 * Date: 2018/4/30
 * Time: 15:29
 */
abstract class Plugin extends View
{
    public $construct_data = array();
    final public function __construct($data=array()) {
        DebugTool::get_instance()->debug("import plugin");
        $this->construct_data = $data;
        $this->include_template($this->get_content(),'plugin');
    }
    public function get_construct_datas() {
        return $this->construct_data;
    }
    public function get_construct_data($key) {
        return $this->construct_data[$key];
    }

    public function get_select() {
        return $this->construct_data['section'];
    }
}

?>