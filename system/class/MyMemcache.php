<?php
/**
 * Created by PhpStorm.
 * User: LIKANG
 * Date: 2017/12/8
 * Time: 16:23
 */

class MyMemcache extends Memcached implements ICache
{

    public function __construct($persistent_id, $callback)
    {
        parent::__construct($persistent_id, $callback);
        cf_require_class("ConfigTool");
    }


    public function set($key, $value, $expire = 86400)
    {
        $cache_status = ConfigTool::get_instance()->get_config('cache');
        if($cache_status){
            return parent::set($key, $value, $expire);
        }
        return false;
    }

    public function get($key)
    {
        $cache_status = ConfigTool::get_instance()->get_config('cache');
        if($cache_status){
            return parent::get($key);
        }
        return false;
    }

    public function set_array($key, $value, $expire = 86400)
    {
        $this->set($key, $value, $expire);
    }

    public function get_array($key)
    {
        $this->get($key);
    }
}