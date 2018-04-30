<?php

cf_require_class("Plugin");
class Demo_FooterPlugin extends Plugin {
    public function get_content() {
        return 'Demo_Footer';
    }

    public static function get_css_list() {
        return array(
            'Demo_Footer'
        );
    }

    public static function get_js_list() {
        return array(
            'Demo_Footer'
        );
    }
}