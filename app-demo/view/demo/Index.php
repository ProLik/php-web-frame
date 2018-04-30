<?php

/**
 * Created by PhpStorm.
 * User: pc
 * Date: 2018/4/30
 * Time: 15:00
 */
class Demo_IndexView extends DemoFrameView
{

    public static function get_css_list() {
        return array_merge(parent::get_css_list(),
            array(
                'Demo_Index'
            ));
    }

    public static function get_js_list() {
        return array_merge(
            parent::get_js_list(),
            array(
                'Demo_Index',
            ));
    }

    public static function get_static_js_list() {
        return array_merge(
            parent::get_static_js_list(),array(
            'js/jquery-ui.min.js'
        ));
    }
    
    public function get_content()
    {
        $data = DPS::get_instance()->get_request()->get_attributes();
        foreach ($data as $k => $v) {
            $this->set_data($k, $v);
        }
        return 'Demo_Index';
    }

    public function get_title() {
        return 'ProLik world';
    }
}