<?php
/**
 * Created by PhpStorm.
 * User: LIKANG
 * Date: 2017/12/8
 * Time: 17:55
 */

class MyRedis implements ICache
{

    public function __construct()
    {
        cf_require_class("ConfigTool");
        cf_require_class("DebugTool");
    }

    private function get_master_redis() {
        return $this->get_redis('master');
    }

    private function get_slave_redis() {
        return $this->get_redis('slave');
    }

    public function get_redis($type = "master"){
        if(self::$$type){
            return self::$$type;
        }

        self::$$type = new Redis();
        $redis_config = $this->get_redis_config($type);
        self::$$type->pconnect($redis_config['host'], $redis_config['port']);
        if($redis_config["password"]){
            self::$$type->auth($redis_config['password']);
        }
        return self::$$type;

    }

    public function set_array($key, $value, $expire = 0)
    {
        if (is_array($value)) {
            $string = serialize($value);
        } else {
            $string = $value;
        }

        if ($expire != 0) {
            $res = $this->set($key, $string, $expire);
        } else {
            $res = $this->set($key, $string);
        }

        DebugTool::get_instance()->debug($res, 'redis');
        return $res;
    }

    public function get_array($key)
    {
        $res = $this->get($key);
        DebugTool::get_instance()->debug($res, 'redis');
        return unserialize($res);
    }

    public function set($key, $value, $expire = 0)
    {
        if ($expire != 0) {
            $res = $this->get_master_redis()->set($key, $value, $expire);
        } else {
            $res = $this->get_master_redis()->set($key, $value);
        }
        DebugTool::get_instance()->debug($res, 'redis');
        return $res;
    }

    public function get($key)
    {
        $res = $this->get_slave_redis()->get($key);
        DebugTool::get_instance()->debug($res, 'redis');
        return $res;
    }

    public function delete($key)
    {
        // TODO: Implement delete() method.
    }

    private function get_redis_config($type = "master")
    {
        $redis_config = ConfigTool::get_instance()->get_config("redis", "common");
        return $redis_config[$type];
    }
}