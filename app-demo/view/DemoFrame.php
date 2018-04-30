<?php

/**
 * Created by PhpStorm.
 * User: pc
 * Date: 2018/4/30
 * Time: 15:00
 */
cf_require_plugin("Demo_Footer");
abstract class DemoFrameView extends View
{
    public static function get_css_list() {
        return array(
            'DemoFrame'
        );
    }

    public static function get_js_list() {
        return array_merge(
            parent::get_js_list(), array(
            'Util'
        ));
    }


    public static function get_static_js_list() {
        return array_merge(
            parent::get_static_js_list(), array(
            'js/jquery-1.7.1.min.js'
        ));
    }

    public static function get_plugin() {
        return array(
            'Demo_Footer',
            'Demo_Header'
        );
    }

    public function get_container() {
        return 'DemoFrame';
    }
}